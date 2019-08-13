<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Arr;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\GitIgnore;
use Symfony\Component\Console\Input\InputArgument;

class EnvCloneCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:clone')
            ->addArgument('from', InputArgument::REQUIRED, 'The environment to clone from')
            ->addArgument('to', InputArgument::REQUIRED, 'The name that should be assigned to the cloned environment')
            ->setDescription('Clone an existing environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $environment = $this->vapor->cloneEnvironment(
            Manifest::id(),
            $this->argument('from'),
            $this->argument('to')
        );

        $manifest = Manifest::current();

        Manifest::addEnvironment(
            $this->argument('to'),
            Arr::except($manifest['environments'][$this->argument('from')] ?? [], 'domain')
        );

        GitIgnore::add(['.env.'.$this->argument('to')]);

        Helpers::info('Environment cloned successfully.');
    }
}
