<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class EnvPushCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:push')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Upload the environment file for the given environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $environment = $this->argument('environment');

        if (! file_exists($file = getcwd().'/.env.'.$environment)) {
            Helpers::abort('The environment variables for that environment have not been downloaded.');
        }

        Helpers::step('<options=bold>Uploading Environment File...</>');

        $this->vapor->updateEnvironmentVariables(
            Manifest::id(),
            $environment,
            file_get_contents($file)
        );

        Helpers::line();
        Helpers::info('Environment variables uploaded successfully.');
        Helpers::line();
        Helpers::line('You must deploy the project for the new variables to take effect.');

        if (Helpers::confirm('Would you like to delete the environment file from your machine')) {
            @unlink($file);
        }
    }
}
