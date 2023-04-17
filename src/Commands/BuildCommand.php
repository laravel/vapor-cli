<?php

namespace Laravel\VaporCli\Commands;

use DateTime;
use Laravel\VaporCli\BuildProcess\BuildContainerImage;
use Laravel\VaporCli\BuildProcess\CollectSecrets;
use Laravel\VaporCli\BuildProcess\CompressApplication;
use Laravel\VaporCli\BuildProcess\CompressVendor;
use Laravel\VaporCli\BuildProcess\ConfigureArtisan;
use Laravel\VaporCli\BuildProcess\ConfigureComposerAutoloader;
use Laravel\VaporCli\BuildProcess\CopyApplicationToBuildPath;
use Laravel\VaporCli\BuildProcess\ExecuteBuildCommands;
use Laravel\VaporCli\BuildProcess\ExtractAssetsToSeparateDirectory;
use Laravel\VaporCli\BuildProcess\ExtractVendorToSeparateDirectory;
use Laravel\VaporCli\BuildProcess\HarmonizeConfigurationFiles;
use Laravel\VaporCli\BuildProcess\InjectErrorPages;
use Laravel\VaporCli\BuildProcess\InjectHandlers;
use Laravel\VaporCli\BuildProcess\InjectRdsCertificate;
use Laravel\VaporCli\BuildProcess\ProcessAssets;
use Laravel\VaporCli\BuildProcess\RemoveIgnoredFiles;
use Laravel\VaporCli\BuildProcess\RemovePintBinary;
use Laravel\VaporCli\BuildProcess\RemoveVendorPlatformCheck;
use Laravel\VaporCli\BuildProcess\SetBuildEnvironment;
use Laravel\VaporCli\BuildProcess\ValidateApiToken;
use Laravel\VaporCli\BuildProcess\ValidateManifest;
use Laravel\VaporCli\BuildProcess\ValidateOctaneDependencies;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->addOption('asset-url', null, InputOption::VALUE_OPTIONAL, 'The asset base URL')
            ->addOption('build-arg', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Docker build argument')
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

        if (Manifest::usesContainerImage($this->argument('environment')) &&
            ! file_exists($file = Path::dockerfile($this->argument('environment')))) {
            Helpers::abort("Please create a Dockerfile at [$file].");
        }

        $startedAt = new DateTime();

        collect([
            new ValidateApiToken(),
            new ValidateManifest($this->argument('environment')),
            new CopyApplicationToBuildPath(),
            new HarmonizeConfigurationFiles(),
            new SetBuildEnvironment($this->argument('environment'), $this->option('asset-url')),
            new ExecuteBuildCommands($this->argument('environment')),
            new ValidateOctaneDependencies($this->argument('environment')),
            new ConfigureArtisan($this->argument('environment')),
            new ConfigureComposerAutoloader($this->argument('environment')),
            new RemoveIgnoredFiles(),
            new RemovePintBinary(),
            new RemoveVendorPlatformCheck(),
            new ProcessAssets($this->option('asset-url')),
            new ExtractAssetsToSeparateDirectory(),
            new InjectHandlers($this->argument('environment')),
            new CollectSecrets($this->argument('environment')),
            new InjectErrorPages(),
            new InjectRdsCertificate(),
            new ExtractVendorToSeparateDirectory($this->argument('environment')),
            new CompressApplication($this->argument('environment')),
            new CompressVendor($this->argument('environment')),
            new BuildContainerImage($this->argument('environment'), $this->option('build-arg'), Manifest::dockerBuildArgs($this->argument('environment'))),
        ])->each->__invoke();

        $time = (new DateTime())->diff($startedAt)->format('%im%Ss');

        Helpers::line();
        Helpers::line('<info>Project built successfully.</info> ('.$time.')');
    }
}
