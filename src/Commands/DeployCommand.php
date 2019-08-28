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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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

        $this->ensureManifestIsValid();

        // First we will build the project and create a new deployment artifact for the
        // project deployment. Once that has been done we can upload the assets into
        // storage so that they can be accessed publicly or displayed on the site.
        $this->serveAssets($artifact = $this->buildProject(
            $this->vapor->project(Manifest::id())
        ));

        (new Filesystem)->deleteDirectory(Path::vapor());

        $deployment = $this->handleCancellations($this->vapor->deploy(
            $artifact['id'], Manifest::current()
        ));

        if ($this->option('without-waiting')) {
            Helpers::line();
            return Helpers::info('Artifact uploaded successfully.');
        }

        $deployment = $this->displayDeploymentProgress($deployment);

        Clipboard::deployment($deployment);
    }

    /**
     * Ensure the current manifest is valid.
     *
     * @return void
     */
    protected function ensureManifestIsValid()
    {
        $this->vapor->validateManifest(
            Manifest::id(), $this->argument('environment'), Manifest::current()
        );
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
        ]);

        return $this->uploadArtifact(
            $this->argument('environment'), $uuid
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

        Helpers::step('<comment>Uploading Deployment Artifact</comment> ('.Helpers::megabytes(Path::artifact()).')');

        $artifact = $this->vapor->createArtifact(
            Manifest::id(),
            $uuid,
            $environment,
            Path::artifact(),
            $this->option('commit') ?: Git::hash(),
            $this->option('message') ?: Git::message()
        );

        Helpers::line();

        Helpers::step('<comment>Uploading Vendor Directory</comment> ('.Helpers::megabytes(Path::vendorArtifact()).')');

        $this->vapor->uploadVendorDirectory(
            $artifact['id'],
            Path::vendorArtifact()
        );

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

        (new ServeAssets)->__invoke($this->vapor, $artifact);
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
}
