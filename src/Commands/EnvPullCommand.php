<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class EnvPullCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:pull')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Download the environment file for the given environment');
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

        Helpers::step('<options=bold>Downloading Environment File...</>');

        file_put_contents(
            getcwd().'/.env.'.$environment,
            trim($this->vapor->environmentVariables(
                Manifest::id(),
                $environment
            )).PHP_EOL
        );

        Helpers::info(PHP_EOL."Environment variables written to [./.env.{$environment}].");
    }
}
