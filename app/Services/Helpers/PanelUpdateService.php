<?php

namespace Pterodactyl\Services\Helpers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
use Pterodactyl\Exceptions\DisplayException;
use Symfony\Component\Process\Process;

class PanelUpdateService
{
    public const STATUS_IDLE = 'idle';
    public const STATUS_STARTING = 'starting';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        private Filesystem $files,
        private SoftwareVersionService $versionService,
    ) {
    }

    public function getOverview(bool $refreshVersion = false): array
    {
        try {
            $this->versionService->refresh($refreshVersion);
            $state = $this->normalizeState($this->readState());
            $latestVersion = $this->versionService->getPanel();
            $ownership = $this->detectOwnership();
            $canRepairOwnership = $this->canRepairOwnership();
            $updateAvailable = $this->hasUpdateAvailable($latestVersion);

            return [
                'status' => Arr::get($state, 'status', self::STATUS_IDLE),
                'status_detail' => Arr::get($state, 'detail'),
                'current_version' => config('app.version'),
                'latest_version' => $latestVersion,
                'target_version' => Arr::get($state, 'target_version', $latestVersion),
                'release_url' => $this->versionService->getPanelReleaseUrl(),
                'update_available' => $updateAvailable,
                'is_running' => $this->isActiveStatus(Arr::get($state, 'status')),
                'can_start' => $this->isSupportedPlatform() && $this->canLaunchUpdater() && $updateAvailable && !$this->isActiveStatus(Arr::get($state, 'status')),
                'supported' => $this->isSupportedPlatform(),
                'started_at' => Arr::get($state, 'started_at'),
                'completed_at' => Arr::get($state, 'completed_at'),
                'pid' => Arr::get($state, 'pid'),
                'detected_user' => Arr::get($state, 'user') ?: $ownership['user'],
                'detected_group' => Arr::get($state, 'group') ?: $ownership['group'],
                'can_repair_ownership' => $canRepairOwnership,
                'will_skip_chown' => Arr::has($state, 'skip_chown')
                    ? (bool) Arr::get($state, 'skip_chown')
                    : !$canRepairOwnership,
                'log_excerpt' => $this->readLogExcerpt(),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return $this->getFallbackOverview('The automatic updater status could not be loaded. Check storage logs and permissions before starting an update.');
        }
    }

    /**
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function startAutomaticUpdate(): array
    {
        if (!$this->isSupportedPlatform()) {
            throw new DisplayException('Automatic updates are only supported on Linux and other Unix-like hosts.');
        }

        if (!$this->canLaunchUpdater()) {
            throw new DisplayException('Unable to launch the automatic updater process from this Panel installation.');
        }

        $overview = $this->getOverview(true);
        if ($overview['is_running']) {
            throw new DisplayException('An automatic update is already running.');
        }

        if (!$overview['update_available']) {
            throw new DisplayException('This Panel is already running the latest available release.');
        }

        $state = [
            'status' => self::STATUS_STARTING,
            'detail' => null,
            'started_at' => CarbonImmutable::now()->toAtomString(),
            'completed_at' => null,
            'updated_at' => CarbonImmutable::now()->toAtomString(),
            'current_version' => config('app.version'),
            'target_version' => $overview['latest_version'],
            'release_url' => $overview['release_url'],
            'user' => $overview['detected_user'],
            'group' => $overview['detected_group'],
            'skip_chown' => !$overview['can_repair_ownership'],
            'pid' => null,
        ];

        $this->writeState($state);
        $this->prepareLog($state);

        try {
            $pid = $this->launchBackgroundUpdater($state);
        } catch (\Throwable $exception) {
            $this->markFailed('Unable to start the background updater process: ' . $exception->getMessage());

            throw new DisplayException('Unable to start the background updater process. Check the Panel logs for details.', $exception);
        }

        $state['pid'] = $pid;
        $state['updated_at'] = CarbonImmutable::now()->toAtomString();
        $this->writeState($state);
        $this->appendLog(sprintf('[%s] Background updater launched with PID %d.', CarbonImmutable::now()->toDateTimeString(), $pid));

        return $this->getOverview();
    }

    public function markRunning(array $context = []): void
    {
        $state = array_merge($this->readState(), $context, [
            'status' => self::STATUS_RUNNING,
            'detail' => null,
            'updated_at' => CarbonImmutable::now()->toAtomString(),
            'completed_at' => null,
            'pid' => getmypid(),
        ]);

        if (blank(Arr::get($state, 'started_at'))) {
            $state['started_at'] = CarbonImmutable::now()->toAtomString();
        }

        $this->writeState($state);
        $this->appendLog(sprintf('[%s] Automatic update is now running.', CarbonImmutable::now()->toDateTimeString()));
    }

    public function markCompleted(): void
    {
        $state = array_merge($this->readState(), [
            'status' => self::STATUS_COMPLETED,
            'detail' => null,
            'updated_at' => CarbonImmutable::now()->toAtomString(),
            'completed_at' => CarbonImmutable::now()->toAtomString(),
        ]);

        $this->writeState($state);
        $this->appendLog(sprintf('[%s] Automatic update finished successfully.', CarbonImmutable::now()->toDateTimeString()));
    }

    public function markFailed(string $detail): void
    {
        $state = array_merge($this->readState(), [
            'status' => self::STATUS_FAILED,
            'detail' => $detail,
            'updated_at' => CarbonImmutable::now()->toAtomString(),
            'completed_at' => CarbonImmutable::now()->toAtomString(),
        ]);

        if (blank(Arr::get($state, 'started_at'))) {
            $state['started_at'] = CarbonImmutable::now()->toAtomString();
        }

        $this->writeState($state);
        $this->appendLog(sprintf('[%s] Automatic update failed: %s', CarbonImmutable::now()->toDateTimeString(), $detail));
    }

    protected function launchBackgroundUpdater(array $state): int
    {
        $arguments = ['--no-interaction'];
        $targetVersion = Arr::get($state, 'target_version');

        if (filled($targetVersion)) {
            $arguments[] = '--release=' . escapeshellarg(ltrim($targetVersion, 'v'));
        }

        if (Arr::get($state, 'skip_chown')) {
            $arguments[] = '--skip-chown';
        } else {
            if (filled(Arr::get($state, 'user'))) {
                $arguments[] = '--user=' . escapeshellarg(Arr::get($state, 'user'));
            }

            if (filled(Arr::get($state, 'group'))) {
                $arguments[] = '--group=' . escapeshellarg(Arr::get($state, 'group'));
            }
        }

        $command = sprintf(
            'nohup %s artisan p:update-panel %s >> %s 2>&1 < /dev/null & echo $!',
            escapeshellarg(PHP_BINARY),
            implode(' ', $arguments),
            escapeshellarg($this->getLogFilePath()),
        );

        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(15);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput() ?: 'Unable to launch the updater shell process.'));
        }

        $pid = trim($process->getOutput());
        if (!preg_match('/^\d+$/', $pid)) {
            throw new \RuntimeException('The updater shell did not return a valid process identifier.');
        }

        return (int) $pid;
    }

    protected function normalizeState(array $state): array
    {
        $state = array_merge([
            'status' => self::STATUS_IDLE,
            'detail' => null,
            'started_at' => null,
            'completed_at' => null,
            'updated_at' => null,
            'current_version' => config('app.version'),
            'target_version' => $this->versionService->getPanel(),
            'release_url' => $this->versionService->getPanelReleaseUrl(),
            'user' => null,
            'group' => null,
            'skip_chown' => !$this->canRepairOwnership(),
            'pid' => null,
        ], $state);

        if ($this->isActiveStatus($state['status']) && $this->hasStalled($state)) {
            $state['status'] = self::STATUS_FAILED;
            $state['detail'] = 'The updater process stopped unexpectedly. Check the updater log for details.';
            $state['completed_at'] = CarbonImmutable::now()->toAtomString();
            $state['updated_at'] = CarbonImmutable::now()->toAtomString();
            $this->writeState($state);
            $this->appendLog(sprintf('[%s] Updater process was marked as failed because it is no longer running.', CarbonImmutable::now()->toDateTimeString()));
        }

        return $state;
    }

    protected function hasUpdateAvailable(string $latestVersion): bool
    {
        if (config('app.version') === 'canary' || blank($latestVersion) || $latestVersion === 'error') {
            return false;
        }

        return version_compare(config('app.version'), $latestVersion, '<');
    }

    protected function hasStalled(array $state): bool
    {
        $pid = (int) Arr::get($state, 'pid', 0);
        if ($pid > 0) {
            return !$this->isProcessRunning($pid);
        }

        $startedAt = Arr::get($state, 'started_at');
        if (blank($startedAt)) {
            return false;
        }

        return CarbonImmutable::parse($startedAt)
            ->addMinutes((int) config('pterodactyl.panel_updater.start_timeout', 2))
            ->isPast();
    }

    protected function isProcessRunning(int $pid): bool
    {
        if ($pid < 1 || !$this->isSupportedPlatform()) {
            return false;
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        return $this->files->isDirectory('/proc/' . $pid);
    }

    protected function detectOwnership(): array
    {
        $user = null;
        $group = null;
        $publicPath = public_path();

        if (!$this->files->exists($publicPath)) {
            return ['user' => $user, 'group' => $group];
        }

        $ownerId = @fileowner($publicPath);
        if ($ownerId !== false && function_exists('posix_getpwuid')) {
            $details = @posix_getpwuid($ownerId);
            $user = $details['name'] ?? null;
        }

        $groupId = @filegroup($publicPath);
        if ($groupId !== false && function_exists('posix_getgrgid')) {
            $details = @posix_getgrgid($groupId);
            $group = $details['name'] ?? null;
        }

        return ['user' => $user, 'group' => $group];
    }

    protected function canRepairOwnership(): bool
    {
        return $this->isSupportedPlatform() && function_exists('posix_geteuid') && posix_geteuid() === 0;
    }

    protected function isSupportedPlatform(): bool
    {
        return DIRECTORY_SEPARATOR === '/';
    }

    protected function canLaunchUpdater(): bool
    {
        return filled(PHP_BINARY)
            && $this->files->exists(base_path('artisan'))
            && $this->isSupportedPlatform();
    }

    protected function isActiveStatus(?string $status): bool
    {
        return in_array($status, [self::STATUS_STARTING, self::STATUS_RUNNING], true);
    }

    protected function readState(): array
    {
        if (!$this->files->exists($this->getStateFilePath())) {
            return [];
        }

        $decoded = json_decode($this->files->get($this->getStateFilePath()), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function writeState(array $state): void
    {
        $this->ensureDirectoryExists(dirname($this->getStateFilePath()));
        $this->files->put(
            $this->getStateFilePath(),
            json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            true,
        );
    }

    protected function prepareLog(array $state): void
    {
        $this->ensureDirectoryExists(dirname($this->getLogFilePath()));

        $lines = [
            sprintf('[%s] Preparing automatic Panel update.', CarbonImmutable::now()->toDateTimeString()),
            sprintf('Current version: %s', config('app.version')),
            sprintf('Target version: %s', Arr::get($state, 'target_version', 'latest')),
            sprintf('Release URL: %s', Arr::get($state, 'release_url', $this->versionService->getPanelReleaseUrl())),
            Arr::get($state, 'skip_chown')
                ? 'Ownership repair: skipped automatically because the web process is not running as root.'
                : sprintf(
                    'Ownership repair: enabled for %s:%s.',
                    Arr::get($state, 'user', 'www-data'),
                    Arr::get($state, 'group', 'www-data'),
                ),
            '',
        ];

        $this->files->put($this->getLogFilePath(), implode(PHP_EOL, $lines), true);
    }

    protected function appendLog(string $line): void
    {
        $this->ensureDirectoryExists(dirname($this->getLogFilePath()));
        $this->files->append($this->getLogFilePath(), $line . PHP_EOL);
    }

    protected function readLogExcerpt(int $lineCount = 40): ?string
    {
        if (!$this->files->exists($this->getLogFilePath())) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($this->files->get($this->getLogFilePath())));
        $lines = array_values(array_filter($lines, static fn ($line) => $line !== null));

        if (empty($lines)) {
            return null;
        }

        return implode(PHP_EOL, array_slice($lines, -$lineCount));
    }

    protected function getStateFilePath(): string
    {
        return config('pterodactyl.panel_updater.state_file', storage_path('app/panel-updater/status.json'));
    }

    protected function getLogFilePath(): string
    {
        return config('pterodactyl.panel_updater.log_file', storage_path('logs/panel-updater.log'));
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if ($this->files->isDirectory($path)) {
            return;
        }

        $this->files->makeDirectory($path, 0755, true);
    }

    protected function getFallbackOverview(?string $detail = null): array
    {
        $latestVersion = $this->versionService->getPanel();
        $ownership = ['user' => null, 'group' => null];

        try {
            $ownership = $this->detectOwnership();
        } catch (\Throwable) {
        }

        return [
            'status' => self::STATUS_FAILED,
            'status_detail' => $detail,
            'current_version' => config('app.version'),
            'latest_version' => $latestVersion,
            'target_version' => $latestVersion,
            'release_url' => $this->versionService->getPanelReleaseUrl(),
            'update_available' => $this->hasUpdateAvailable($latestVersion),
            'is_running' => false,
            'can_start' => false,
            'supported' => $this->isSupportedPlatform(),
            'started_at' => null,
            'completed_at' => null,
            'pid' => null,
            'detected_user' => $ownership['user'],
            'detected_group' => $ownership['group'],
            'can_repair_ownership' => $this->canRepairOwnership(),
            'will_skip_chown' => !$this->canRepairOwnership(),
            'log_excerpt' => null,
        ];
    }
}
