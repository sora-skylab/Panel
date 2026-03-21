<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Pterodactyl\Console\Kernel;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\ProgressBar;

class UpgradeCommand extends Command
{
    protected $signature = 'p:upgrade
        {--user= : The user that PHP runs under. All files will be owned by this user.}
        {--group= : The group that PHP runs under. All files will be owned by this group.}
        {--url= : The specific archive to download.}
        {--release= : A specific Pterodactyl version to download from GitHub. Leave blank to use latest.}
        {--skip-download : If set no archive will be downloaded.}
        {--skip-chown : Skip resetting file ownership after the upgrade.}
        {--down-first : Place the panel into maintenance mode before downloading the archive.}';

    protected $description = 'Downloads a new archive for Pterodactyl from GitHub and then executes the normal upgrade commands.';

    /**
     * Executes an upgrade command which will run through all of our standard
     * commands for Pterodactyl and enable users to basically just download
     * the archive and execute this and be done.
     *
     * This places the application in maintenance mode as well while the commands
     * are being executed.
     *
     * @throws \Throwable
     */
    public function handle(): int
    {
        $skipDownload = (bool) $this->option('skip-download');
        $skipChown = (bool) $this->option('skip-chown');
        $downFirst = (bool) $this->option('down-first');

        if (!$skipDownload) {
            $this->output->warning('This command does not verify the integrity of downloaded assets. Please ensure that you trust the download source before continuing. If you do not wish to download an archive, please indicate that using the --skip-download flag, or answering "no" to the question below.');
            $this->output->comment('Download Source (set with --url=):');
            $this->line($this->getUrl());
        }

        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            $this->error('Cannot execute self-upgrade process. The minimum required PHP version required is 8.2.0, you have [' . PHP_VERSION . '].');

            return self::FAILURE;
        }

        $ownership = $this->resolveOwnership($skipChown, $skipDownload);
        if (is_null($ownership)) {
            return self::SUCCESS;
        }

        [$user, $group] = $ownership;

        ini_set('output_buffering', '0');
        $bar = $this->output->createProgressBar($this->countProgressSteps($skipDownload, $skipChown));
        $bar->start();

        $maintenanceModeEnabled = false;

        try {
            if ($downFirst) {
                $this->withProgress($bar, function () {
                    $this->callCommand('down', [], '$upgrader> php artisan down');
                });
                $maintenanceModeEnabled = true;
            }

            if (!$skipDownload) {
                $this->withProgress($bar, function () {
                    $this->runProcess(
                        'curl -L "' . $this->getUrl() . '" | tar -xzv',
                        '$upgrader> curl -L "' . $this->getUrl() . '" | tar -xzv',
                    );
                });
            }

            if (!$downFirst) {
                $this->withProgress($bar, function () {
                    $this->callCommand('down', [], '$upgrader> php artisan down');
                });
                $maintenanceModeEnabled = true;
            }

            $this->withProgress($bar, function () {
                $this->runProcess(
                    ['chmod', '-R', '755', 'storage', 'bootstrap/cache'],
                    '$upgrader> chmod -R 755 storage bootstrap/cache',
                );
            });

            $this->withProgress($bar, function () {
                $command = ['composer', 'install', '--no-ansi'];
                if (config('app.env') === 'production' && !config('app.debug')) {
                    $command[] = '--optimize-autoloader';
                    $command[] = '--no-dev';
                }

                $this->runProcess($command, '$upgrader> ' . implode(' ', $command), null, 10 * 60);
            });

            $this->rebootstrapApplication();

            $this->withProgress($bar, function () {
                $this->callCommand('view:clear', [], '$upgrader> php artisan view:clear');
            });

            $this->withProgress($bar, function () {
                $this->callCommand('config:clear', [], '$upgrader> php artisan config:clear');
            });

            $this->withProgress($bar, function () {
                $this->callCommand('migrate', ['--force' => true, '--seed' => true], '$upgrader> php artisan migrate --force --seed');
            });

            if (!$skipChown) {
                $this->withProgress($bar, function () use ($user, $group) {
                    $this->runProcess(
                        'chown -R ' . escapeshellarg($user . ':' . $group) . ' *',
                        '$upgrader> chown -R ' . $user . ':' . $group . ' *',
                        $this->getLaravel()->basePath(),
                        10 * 60,
                    );
                });
            }

            $this->withProgress($bar, function () {
                $this->callCommand('queue:restart', [], '$upgrader> php artisan queue:restart');
            });

            $this->withProgress($bar, function () {
                $this->callCommand('up', [], '$upgrader> php artisan up');
            });
            $maintenanceModeEnabled = false;

            $this->newLine(2);
            $this->info('Panel has been successfully upgraded. Please ensure you also update any Wings instances: https://pterodactyl.io/wings/1.0/upgrading.html');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->newLine(2);
            $this->error('Upgrade process failed: ' . $exception->getMessage());

            if ($maintenanceModeEnabled) {
                try {
                    $this->callCommand('up', [], '$upgrader> php artisan up');
                    $this->warn('Maintenance mode has been disabled after the failed upgrade attempt.');
                } catch (\Throwable $restoreException) {
                    $this->error('Failed to exit maintenance mode automatically: ' . $restoreException->getMessage());
                }
            }

            throw $exception;
        }
    }

    protected function countProgressSteps(bool $skipDownload, bool $skipChown): int
    {
        return 8 + ($skipDownload ? 0 : 1) + ($skipChown ? 0 : 1);
    }

    /**
     * @throws \RuntimeException
     */
    protected function callCommand(string $command, array $arguments, string $displayCommand): void
    {
        $this->line($displayCommand);
        $exitCode = $this->call($command, $arguments);

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf('The command [%s] exited with status code %d.', $command, $exitCode));
        }
    }

    /**
     * @throws \RuntimeException
     */
    protected function runProcess(array|string $command, string $displayCommand, ?string $workingDirectory = null, ?int $timeout = null): void
    {
        $this->line($displayCommand);
        $workingDirectory = $workingDirectory ?: $this->getLaravel()->basePath();

        $process = is_array($command)
            ? new Process($command, $workingDirectory)
            : Process::fromShellCommandline($command, $workingDirectory);

        if (!is_null($timeout)) {
            $process->setTimeout($timeout);
        }

        $process->run(function ($type, $buffer) {
            $this->{$type === Process::ERR ? 'error' : 'line'}($buffer);
        });

        if (!$process->isSuccessful()) {
            $message = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new \RuntimeException($message !== '' ? $message : sprintf('The process [%s] failed with exit code %d.', $displayCommand, $process->getExitCode()));
        }
    }

    protected function rebootstrapApplication(): void
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();
        $this->setLaravel($app);
    }

    protected function withProgress(ProgressBar $bar, \Closure $callback): void
    {
        $bar->clear();
        $callback();
        $bar->advance();
        $bar->display();
    }

    protected function resolveOwnership(bool $skipChown, bool &$skipDownload): ?array
    {
        $detectedUser = $this->detectOwnerName();
        $detectedGroup = $this->detectGroupName();
        $user = $this->option('user') ?: $detectedUser ?: 'www-data';
        $group = $this->option('group') ?: $detectedGroup ?: 'www-data';

        if (!$this->input->isInteractive()) {
            return [$user, $group];
        }

        if (!$skipDownload) {
            $skipDownload = !$this->confirm('Would you like to download and unpack the archive files for the latest version?', true);
        }

        if (!$skipChown && is_null($this->option('user'))) {
            if (!$this->confirm("Your webserver user has been detected as <fg=blue>[{$user}]:</> is this correct?", true)) {
                $user = $this->anticipate(
                    'Please enter the name of the user running your webserver process. This varies from system to system, but is generally "www-data", "nginx", or "apache".',
                    [
                        'www-data',
                        'nginx',
                        'apache',
                    ]
                );
            }
        }

        if (!$skipChown && is_null($this->option('group'))) {
            if (!$this->confirm("Your webserver group has been detected as <fg=blue>[{$group}]:</> is this correct?", true)) {
                $group = $this->anticipate(
                    'Please enter the name of the group running your webserver process. Normally this is the same as your user.',
                    [
                        'www-data',
                        'nginx',
                        'apache',
                    ]
                );
            }
        }

        if (!$this->confirm('Are you sure you want to run the upgrade process for your Panel?')) {
            $this->warn('Upgrade process terminated by user.');

            return null;
        }

        return [$user, $group];
    }

    protected function detectOwnerName(): ?string
    {
        $publicPath = public_path();
        $ownerId = @fileowner($publicPath);
        if ($ownerId === false || !function_exists('posix_getpwuid')) {
            return null;
        }

        $details = @posix_getpwuid($ownerId);

        return $details['name'] ?? null;
    }

    protected function detectGroupName(): ?string
    {
        $publicPath = public_path();
        $groupId = @filegroup($publicPath);
        if ($groupId === false || !function_exists('posix_getgrgid')) {
            return null;
        }

        $details = @posix_getgrgid($groupId);

        return $details['name'] ?? null;
    }

    protected function getUrl(): string
    {
        if ($this->option('url')) {
            return $this->option('url');
        }

        $releasesUrl = rtrim(config('pterodactyl.versioning.panel.releases_url'), '/');
        $downloadPath = $this->option('release') ? 'download/v' . $this->option('release') : 'latest/download';

        return sprintf('%s/%s/panel.tar.gz', $releasesUrl, $downloadPath);
    }
}
