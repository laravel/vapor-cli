<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Git;
use Laravel\VaporCli\Path;
use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Illuminate\Support\Carbon;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Clipboard;
use Laravel\VaporCli\ServeAssets;
use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\Aws\AwsStorageProvider;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DeployGroupCommand extends Command
{
    use DisplaysDeploymentProgress;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('deploy:group')
            ->addArgument('group', InputArgument::REQUIRED, 'The group name')
            ->setDescription('Deploy an environment group');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $environments = Manifest::getEnvironmentsByGroup($this->argument('group'));
        
        foreach ($environments as $environment) {
            $this->deployEnvironment($environment);
        }
    }

    protected function deployEnvironment(string $environment)
    {
        $this->call('deploy', [
            'environment' => $environment,
        ]);
    }
}
