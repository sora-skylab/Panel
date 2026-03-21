<?php

namespace Pterodactyl\Console\Commands\Panel;

use Illuminate\Console\Command;
use Pterodactyl\Services\Helpers\PanelUpdateService;

class UpdatePanelCommand extends Command
{
    protected $signature = 'p:update-panel
        {--release= : The specific Panel release to install.}
        {--user= : The user that should own the files after the upgrade.}
        {--group= : The group that should own the files after the upgrade.}
        {--skip-chown : Skip repairing file ownership after the upgrade.}';

    protected $description = 'Runs the automatic Panel updater in the background for the admin control panel.';

    public function __construct(private PanelUpdateService $panelUpdateService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = array_filter([
            'target_version' => $this->option('release') ?: null,
            'user' => $this->option('user') ?: null,
            'group' => $this->option('group') ?: null,
            'skip_chown' => (bool) $this->option('skip-chown'),
        ], static fn ($value) => !is_null($value));

        $this->panelUpdateService->markRunning($context);

        try {
            $arguments = [
                '--down-first' => true,
                '--no-interaction' => true,
            ];

            if ($this->option('release')) {
                $arguments['--release'] = $this->option('release');
            }

            if ($this->option('user')) {
                $arguments['--user'] = $this->option('user');
            }

            if ($this->option('group')) {
                $arguments['--group'] = $this->option('group');
            }

            if ($this->option('skip-chown')) {
                $arguments['--skip-chown'] = true;
            }

            $exitCode = $this->call('p:upgrade', $arguments);

            if ($exitCode !== 0) {
                throw new \RuntimeException('The underlying upgrade command exited with status code ' . $exitCode . '.');
            }

            $this->panelUpdateService->markCompleted();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->panelUpdateService->markFailed($exception->getMessage());
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
