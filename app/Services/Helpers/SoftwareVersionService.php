<?php

namespace Pterodactyl\Services\Helpers;

use GuzzleHttp\Client;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class SoftwareVersionService
{
    public const VERSION_CACHE_KEY = 'pterodactyl:versioning_data';

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
        return $this->cache->remember(self::VERSION_CACHE_KEY, CarbonImmutable::now()->addMinutes(config('pterodactyl.versioning.cache_time', 60)), function () {
            try {
                $panelRelease = $this->requestReleaseData(config('pterodactyl.versioning.panel.latest_url'));
                $wingsRelease = $this->requestReleaseData(config('pterodactyl.versioning.wings.latest_url'));

                return [
                    'panel' => $this->normalizeReleaseVersion(Arr::get($panelRelease, 'tag_name')) ?? config('app.version', 'error'),
                    'wings' => $this->normalizeReleaseVersion(Arr::get($wingsRelease, 'tag_name')) ?? 'error',
                    'panel_release_url' => Arr::get($panelRelease, 'html_url', config('pterodactyl.versioning.panel.releases_url')),
                    'panel_repository_url' => config('pterodactyl.versioning.panel.repository_url'),
                    'discord' => config('pterodactyl.versioning.support.discord_url'),
                    'donations' => config('pterodactyl.versioning.support.donations_url'),
                ];
            } catch (\Exception) {
                return [
                    'panel' => config('app.version', 'error'),
                    'wings' => 'error',
                    'panel_release_url' => config('pterodactyl.versioning.panel.releases_url'),
                    'panel_repository_url' => config('pterodactyl.versioning.panel.repository_url'),
                    'discord' => config('pterodactyl.versioning.support.discord_url'),
                    'donations' => config('pterodactyl.versioning.support.donations_url'),
                ];
            }
        });
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

    protected function normalizeReleaseVersion(?string $version): ?string
    {
        if (blank($version)) {
            return null;
        }

        return ltrim($version, 'v');
    }
}
