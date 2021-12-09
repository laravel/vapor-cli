<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Docker;
use Laravel\VaporCli\Git;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Laravel\VaporCli\ServeAssets;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeployCommand extends Command
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
            ->setName('deploy')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('commit', null, InputOption::VALUE_OPTIONAL, 'The commit hash that is being deployed')
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'The message for the commit that is being deployed')
            ->addOption('without-waiting', null, InputOption::VALUE_NONE, 'Deploy without waiting for progress')
            ->addOption('fresh-assets', null, InputOption::VALUE_NONE, 'Upload a fresh copy of all assets')
            ->addOption('build-arg', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Docker build argument')
            ->setDescription('Deploy an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->ensureProjectDependenciesAreInstalled();
        $this->ensureManifestIsValid();

        // First we will build the project and create a new deployment artifact for the
        // project deployment. Once that has been done we can upload the assets into
        // storage so that they can be accessed publicly or displayed on the site.
        $this->serveAssets($artifact = $this->buildProject(
            $this->vapor->project(Manifest::id())
        ));

        (new Filesystem())->deleteDirectory(Path::vapor());

        $deployment = $this->handleCancellations($this->vapor->deploy(
            $artifact['id'],
            Manifest::current()
        ));

        if ($this->option('without-waiting')) {
            Helpers::line();

            return Helpers::info('Artifact uploaded successfully.');
        }

        $deployment = $this->displayDeploymentProgress($deployment);

        if ($deployment['status'] == 'failed') {
            exit(1);
        }
    }

    /**
     * Ensure the current manifest is valid.
     *
     * @return void
     */
    protected function ensureManifestIsValid()
    {
        $this->vapor->validateManifest(
            Manifest::id(),
            $this->argument('environment'),
            Manifest::current(),
            $this->getCliVersion(),
            $this->getCoreVersion()
        );
    }

    /**
     * Ensure the project's dependencies are installed.
     *
     * @return void
     */
    protected function ensureProjectDependenciesAreInstalled()
    {
        if (! file_exists(Path::current().'/vendor/composer/installed.json')) {
            Helpers::abort(
                'Unable to find your project\'s dependencies. Please run the composer "install" command first.'
            );
        }
    }

    /**
     * Build the project and create a new artifact for the deployment.
     *
     * @param  array  $project
     * @return array
     */
    protected function buildProject(array $project)
    {
        $uuid = (string) Str::uuid();

        $this->call('build', [
            'environment' => $this->argument('environment'),
            '--asset-url' => $this->assetDomain($project).'/'.$uuid,
            '--manifest' => Path::manifest(),
            '--build-arg' => $this->option('build-arg'),
        ]);

        return $this->uploadArtifact(
            $this->argument('environment'),
            $uuid
        );
    }

    /**
     * Get the proper asset domain for the given project.
     *
     * @param  array  $project
     * @return string
     */
    protected function assetDomain(array $project)
    {
        if ($this->usesCloudFront() && $project['cloudfront_status'] == 'deployed') {
            return $project['asset_domains']['cloudfront'] ??
                    $project['asset_domains']['s3'];
        }

        return $project['asset_domains']['s3'];
    }

    /**
     * Determine if the environment being deployed uses CloudFront.
     *
     * @return bool
     */
    protected function usesCloudFront()
    {
        return Manifest::current()['environments'][$this->argument('environment')]['cloudfront'] ?? true;
    }

    /**
     * Upload the deployment artifact.
     *
     * @param  string  $environment
     * @param  string  $uuid
     * @return array
     */
    protected function uploadArtifact($environment, $uuid)
    {
        Helpers::line();

        if (! Manifest::usesContainerImage($environment)) {
            Helpers::step('<comment>Uploading Deployment Artifact</comment> ('.Helpers::megabytes(Path::artifact()).')');
        }

        $artifact = $this->vapor->createArtifact(
            Manifest::id(),
            $uuid,
            $environment,
            Manifest::usesContainerImage($environment) ? null : Path::artifact(),
            $this->option('commit') ?: Git::hash(),
            $this->option('message') ?: Git::message(),
            Manifest::shouldSeparateVendor($environment) ? $this->createVendorHash() : null,
            $this->getCliVersion(),
            $this->getCoreVersion()
        );

        if (isset($artifact['vendor_url'])) {
            Helpers::line();

            Helpers::step('<comment>Uploading Vendor Directory</comment> ('.Helpers::megabytes(Path::vendorArtifact()).')');

            Helpers::app(AwsStorageProvider::class)->store($artifact['vendor_url'], [], Path::vendorArtifact(), true);
        }

        if (Manifest::usesContainerImage($environment)) {
            Helpers::line();

            Helpers::step('<comment>Pushing Container Image</comment>');

            Docker::publish(
                Path::app(),
                Manifest::name(),
                $environment,
                $artifact['container_registry_token'],
                $artifact['container_repository'],
                $artifact['container_image_tag']);
        }

        return $artifact;
    }

    /**
     * Serve the artifact's assets at the given path.
     *
     * @param  array  $artifact
     * @return void
     */
    protected function serveAssets(array $artifact)
    {
        Helpers::line();

        (new ServeAssets())->__invoke($this->vapor, $artifact, $this->option('fresh-assets'));
    }

    /**
     * Setup a signal listener to handle deployment cancellations.
     *
     * @param  array  $deployment
     * @return array
     */
    protected function handleCancellations(array $deployment)
    {
        if (! extension_loaded('pcntl')) {
            return $deployment;
        }

        pcntl_async_signals(true);

        pcntl_signal(SIGINT, function () use ($deployment) {
            $this->cancelDeployment($deployment);

            exit;
        });

        return $deployment;
    }

    /**
     * Attempt to cancel the given deployment.
     *
     * @param  array  $deployment
     * @return void
     */
    protected function cancelDeployment(array $deployment)
    {
        $this->vapor->cancelDeployment($deployment['id']);

        Helpers::line();
        Helpers::danger('Attempting to cancel deployment...');

        $cancellingAt = Carbon::now();

        do {
            $deployment = $this->vapor->deployment($deployment['id']);

            if ($deployment['has_ended'] && $deployment['status'] == 'cancelled') {
                return Helpers::comment('Deployment cancelled successfully.');
            } elseif ($deployment['has_ended'] || Carbon::now()->subSeconds(10)->gte($cancellingAt)) {
                return Helpers::danger('Vapor was unable to cancel the deployment.');
            }

            sleep(3);
        } while (! $deployment['has_ended']);
    }

    /**
     * Create a hash for the vendor directory.
     *
     * @return string
     */
    protected function createVendorHash()
    {
        return md5(md5_file(Path::app().'/composer.json').md5_file(Path::app().'/composer.lock').md5_file(Path::vendor().'/composer/installed.json').md5_file(Path::vendor().'/composer/autoload_real.php'));
    }

    /**
     * Get the version of vapor-cli.
     *
     * @return string
     */
    protected function getCliVersion()
    {
        return $this->getApplication()->getVersion();
    }

    /**
     * Get the version of vapor-core.
     *
     * @return string|null
     */
    protected function getCoreVersion()
    {
        if (! file_exists($file = Path::current().'/vendor/composer/installed.json')) {
            return;
        }

        $version = collect(json_decode(file_get_contents($file)))
                ->pipe(function ($composer) {
                    return collect($composer->get('packages', $composer));
                })
                ->where('name', 'laravel/vapor-core')
                ->first()->version;

        return ltrim($version, 'v');
    }
}
