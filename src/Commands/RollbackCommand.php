<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends Command
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
            ->setName('rollback')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('select', null, InputOption::VALUE_NONE, 'Present a list of deployments to choose from')
            ->setDescription('Rollback an environment to a previous deployment');
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

        // First we will determine which deployment the user would like to roll back
        // to by presenting them with a array of available deployments which they
        // will choose from. We'll present a selection menu to accomplish this.
        $deployments = $this->availableDeployments(
            $environment
        );

        $id = ! $this->option('select') ? $deployments[0]['id'] : $this->menu(
            'Which deployment would you like to rollback to?',
            $this->formatDeployments($deployments)
        );

        Helpers::step('<options=bold>Initiating Rollback</>');

        // Once the rollback is running we will show the deployment pipeline as when
        // running a typical deployment. After the deployment is over we can push
        // the vanity URL into the clipboard for easy access by the developers.
        $deployment = $this->displayDeploymentProgress(
            $this->vapor->rollbackTo($id)
        );
    }

    /**
     * Get the available deployments for the environment.
     *
     * @param  string  $environment
     * @return array
     */
    protected function availableDeployments($environment)
    {
        $deployments = $this->vapor->deployments(
            Manifest::id(),
            $environment
        );

        if (empty($deployments)) {
            Helpers::abort('No deployments exist for this environment.');
        }

        $deployments = collect($deployments)->filter(function ($deployment) {
            return $deployment['status'] == 'finished';
        })->slice(1)->values()->all();

        if (empty($deployments)) {
            Helpers::abort('There are no deployments available for rollback.');
        }

        return $deployments;
    }

    /**
     * Format the deployments into an array of choices.
     *
     * @param  array  $deployments
     * @return array
     */
    protected function formatDeployments(array $deployments)
    {
        return collect($deployments)->mapWithKeys(function ($deployment) {
            return [$deployment['id'] => $this->deploymentName($deployment)];
        })->all();
    }

    /**
     * Get the displayable deployment name for the given deployment.
     *
     * @param  array  $deployment
     * @return string
     */
    protected function deploymentName(array $deployment)
    {
        return trim(sprintf(
            '%s - %s%s (%s)',
            Str::limit($deployment['artifact']['commit_message'] ?? '', 40) ?: 'No commit information',
            $deployment['initiator']['name'],
            $deployment['artifact']['commit'] ? ' ('.Str::substr($deployment['artifact']['commit'], 0, 8).')' : '',
            Helpers::time_ago($deployment['created_at'])
        ));
    }
}
