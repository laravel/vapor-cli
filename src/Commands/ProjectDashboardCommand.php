<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Process\Process;

class ProjectDashboardCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('project:dashboard')
            ->setDescription('Open the current project in Vapor\'s dashboard');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $url = 'https://vapor.laravel.com/app/projects/'.Manifest::id();
        Helpers::info("Opening $url in the default browser...");
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            Process::fromShellCommandline('start '.escapeshellarg($url))->run();
        } else {
            Process::fromShellCommandline('open '.escapeshellarg($url))->run();
        }
    }
}
