<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\GitIgnore;
use Symfony\Component\Console\Input\InputArgument;

class EnvCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Create a new environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->createEnvironment(
            Manifest::id(), $environment = $this->argument('environment')
        );

        Manifest::addEnvironment($environment);

        GitIgnore::add(['.env.'.$environment]);

        Helpers::info('Environment created successfully.');
    }
}
