<?php

namespace Laravel\VaporCli\Commands;

use DateTime;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\BuildProcess\ProcessAssets;
use Symfony\Component\Console\Input\InputOption;
use Laravel\VaporCli\BuildProcess\InjectHandlers;
use Laravel\VaporCli\BuildProcess\CollectSecrets;
use Laravel\VaporCli\BuildProcess\CompressVendor;
use Symfony\Component\Console\Input\InputArgument;
use Laravel\VaporCli\BuildProcess\ConfigureArtisan;
use Laravel\VaporCli\BuildProcess\InjectErrorPages;
use Laravel\VaporCli\BuildProcess\RemoveIgnoredFiles;
use Laravel\VaporCli\BuildProcess\CompressApplication;
use Laravel\VaporCli\BuildProcess\SetBuildEnvironment;
use Laravel\VaporCli\BuildProcess\ExecuteBuildCommands;
use Laravel\VaporCli\BuildProcess\InjectRdsCertificate;
use Laravel\VaporCli\BuildProcess\CopyApplicationToBuildPath;
use Laravel\VaporCli\BuildProcess\ConfigureComposerAutoloader;
use Laravel\VaporCli\BuildProcess\HarmonizeConfigurationFiles;
use Laravel\VaporCli\BuildProcess\ExtractAssetsToSeparateDirectory;
use Laravel\VaporCli\BuildProcess\ExtractVendorToSeparateDirectory;

class BuildCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('build')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('asset-url', null, InputOption::VALUE_OPTIONAL, 'The asset base URL')
            ->setDescription('Build the project archive');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        Helpers::line('Building project...');

        $startedAt = new DateTime;

        collect([
            new CopyApplicationToBuildPath,
            new HarmonizeConfigurationFiles,
            new SetBuildEnvironment($this->argument('environment'), $this->option('asset-url')),
            new ExecuteBuildCommands($this->argument('environment')),
            new ConfigureArtisan,
            new ConfigureComposerAutoloader,
            new RemoveIgnoredFiles,
            new ProcessAssets($this->option('asset-url')),
            new ExtractAssetsToSeparateDirectory,
            new InjectHandlers,
            new CollectSecrets($this->argument('environment')),
            new InjectErrorPages,
            new InjectRdsCertificate,
            new ExtractVendorToSeparateDirectory,
            new CompressApplication,
            new CompressVendor,
        ])->each->__invoke();

        $time = (new DateTime)->diff($startedAt)->format('%im%Ss');

        Helpers::line();
        Helpers::line('<info>Project built successfully.</info> ('.$time.')');
    }
}
