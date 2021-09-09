<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'File to upload the environment variables from')
            ->addOption('keep', null, InputOption::VALUE_NONE, 'Do not delete the environment file after pushing')
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

        $file = $this->option('file') ?: getcwd().'/.env.'.$environment;

        if (! file_exists($file)) {
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

        if ($this->option('keep') !== true && Helpers::confirm('Would you like to delete the environment file from your machine')) {
            @unlink($file);
        }
    }
}
