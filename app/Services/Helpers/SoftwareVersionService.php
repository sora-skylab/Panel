<?php

namespace Pterodactyl\Services\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class SoftwareVersionService
{
    public const VERSION_CACHE_KEY = 'pterodactyl:versioning_data:v2';
    public const FAILURE_CACHE_MINUTES = 5;

    private static array $result;

    /**
     * SoftwareVersionService constructor.
     */
    public function __construct(
        protected CacheRepository $cache,
        protected Client $client,
    ) {
        self::$result = $this->cacheVersionData();
    }

    public function refresh(bool $force = false): array
    {
        if (!$force) {
            return self::$result;
        }

        $cached = $this->cache->get(self::VERSION_CACHE_KEY);
        $data = $this->fetchVersionData();
        if (!Arr::get($data, 'fetched', false) && $this->canReuseCachedVersionData($cached)) {
            $data = $this->reuseCachedVersionData($cached);
        }

        $ttl = CarbonImmutable::now()->addMinutes(
            Arr::get($data, 'fetched', false)
                ? config('pterodactyl.versioning.cache_time', 60)
                : min(config('pterodactyl.versioning.cache_time', 60), self::FAILURE_CACHE_MINUTES),
        );

        $this->cache->put(self::VERSION_CACHE_KEY, $data, $ttl);
        self::$result = $data;

        return self::$result;
    }

    /**
     * Get the latest version of the panel from the CDN servers.
     */
    public function getPanel(): string
    {
        return Arr::get(self::$result, 'panel', config('app.version', 'error'));
    }

    /**
     * Get the latest version of the daemon from the CDN servers.
     */
    public function getDaemon(): string
    {
        return Arr::get(self::$result, 'wings') ?? 'error';
    }

    /**
     * Get the URL to the latest Panel release.
     */
    public function getPanelReleaseUrl(): string
    {
        return Arr::get(self::$result, 'panel_release_url')
            ?? config('pterodactyl.versioning.panel.releases_url');
    }

    /**
     * Get the Panel repository URL.
     */
    public function getPanelRepositoryUrl(): string
    {
        return Arr::get(self::$result, 'panel_repository_url')
            ?? config('pterodactyl.versioning.panel.repository_url');
    }

    /**
     * Get the URL to the discord server.
     */
    public function getDiscord(): string
    {
        return Arr::get(self::$result, 'discord')
            ?? config('pterodactyl.versioning.support.discord_url', 'https://pterodactyl.io/discord');
    }

    /**
     * Get the URL for donations.
     */
    public function getDonations(): string
    {
        return Arr::get(self::$result, 'donations')
            ?? config('pterodactyl.versioning.support.donations_url', 'https://github.com/sponsors/matthewpi');
    }

    /**
     * Determine if the current version of the panel is the latest.
     */
    public function isLatestPanel(): bool
    {
        if (config('app.version') === 'canary') {
            return true;
        }

        return version_compare(config('app.version'), $this->getPanel()) >= 0;
    }

    /**
     * Determine if a passed daemon version string is the latest.
     */
    public function isLatestDaemon(string $version): bool
    {
        if ($version === 'develop') {
            return true;
        }

        return version_compare($version, $this->getDaemon()) >= 0;
    }

    /**
     * Keeps the versioning cache up-to-date with the latest results from the CDN.
     */
    protected function cacheVersionData(): array
    {
        $cached = $this->cache->get(self::VERSION_CACHE_KEY);

        if ($this->isVersionDataValid($cached)) {
            return $cached;
        }

        return $this->refresh(true);
    }

    protected function fetchVersionData(): array
    {
        try {
            $panelRelease = $this->requestPanelReleaseData();
            $wingsRelease = $this->requestReleaseData(config('pterodactyl.versioning.wings.latest_url'));

            return [
                'panel' => $this->normalizeReleaseVersion(Arr::get($panelRelease, 'tag_name')) ?? config('app.version', 'error'),
                'wings' => $this->normalizeReleaseVersion(Arr::get($wingsRelease, 'tag_name')) ?? 'error',
                'panel_release_url' => Arr::get($panelRelease, 'html_url', config('pterodactyl.versioning.panel.releases_url')),
                'panel_repository_url' => config('pterodactyl.versioning.panel.repository_url'),
                'discord' => config('pterodactyl.versioning.support.discord_url'),
                'donations' => config('pterodactyl.versioning.support.donations_url'),
                'resolved_against' => config('app.version', 'error'),
                'fetched' => filled(Arr::get($panelRelease, 'tag_name')) || filled(Arr::get($wingsRelease, 'tag_name')),
            ];
        } catch (\Exception) {
            return [
                'panel' => config('app.version', 'error'),
                'wings' => 'error',
                'panel_release_url' => config('pterodactyl.versioning.panel.releases_url'),
                'panel_repository_url' => config('pterodactyl.versioning.panel.repository_url'),
                'discord' => config('pterodactyl.versioning.support.discord_url'),
                'donations' => config('pterodactyl.versioning.support.donations_url'),
                'resolved_against' => config('app.version', 'error'),
                'fetched' => false,
            ];
        }
    }

    protected function requestPanelReleaseData(): array
    {
        $release = $this->requestReleaseData(config('pterodactyl.versioning.panel.latest_url'));
        if (filled(Arr::get($release, 'tag_name'))) {
            return $release;
        }

        return $this->requestReleaseDataFromLatestPage(config('pterodactyl.versioning.panel.releases_url'));
    }

    protected function isVersionDataValid(mixed $data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        return filled(Arr::get($data, 'panel'))
            && filled(Arr::get($data, 'panel_release_url'))
            && Arr::get($data, 'panel_repository_url') === config('pterodactyl.versioning.panel.repository_url')
            && Arr::get($data, 'resolved_against') === config('app.version', 'error');
    }

    protected function requestReleaseData(?string $url): array
    {
        if (blank($url)) {
            return [];
        }

        $response = $this->client->request('GET', $url, [
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => sprintf('Panel/%s', config('app.version', 'canary')),
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }

    protected function requestReleaseDataFromLatestPage(?string $releasesUrl): array
    {
        if (blank($releasesUrl)) {
            return [];
        }

        $effectiveUri = null;
        $response = $this->client->request('GET', rtrim($releasesUrl, '/') . '/latest', [
            'http_errors' => false,
            'headers' => [
                'User-Agent' => sprintf('Panel/%s', config('app.version', 'canary')),
            ],
            'on_stats' => static function (TransferStats $stats) use (&$effectiveUri): void {
                $effectiveUri = (string) $stats->getEffectiveUri();
            },
        ]);

        if ($response->getStatusCode() !== 200 || blank($effectiveUri)) {
            return [];
        }

        $tagName = $this->extractReleaseTagFromUrl($effectiveUri);
        if (blank($tagName)) {
            return [];
        }

        return [
            'tag_name' => $tagName,
            'html_url' => $effectiveUri,
        ];
    }

    protected function extractReleaseTagFromUrl(string $url): ?string
    {
        if (preg_match('#/(?:releases/)?tag/([^/?#]+)$#', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    protected function canReuseCachedVersionData(mixed $data): bool
    {
        return is_array($data)
            && (bool) Arr::get($data, 'fetched', false)
            && filled(Arr::get($data, 'panel_release_url'))
            && Arr::get($data, 'panel_repository_url') === config('pterodactyl.versioning.panel.repository_url');
    }

    protected function reuseCachedVersionData(array $data): array
    {
        $cachedVersion = Arr::get($data, 'panel');
        $currentVersion = config('app.version', 'error');

        if (blank($cachedVersion) || $cachedVersion === 'error') {
            $data['panel'] = $currentVersion;
        } elseif ($currentVersion !== 'canary' && version_compare($cachedVersion, $currentVersion, '<')) {
            $data['panel'] = $currentVersion;
        }

        $data['resolved_against'] = $currentVersion;

        return $data;
    }

    protected function normalizeReleaseVersion(?string $version): ?string
    {
        if (blank($version)) {
            return null;
        }

        return ltrim($version, 'v');
    }
}
