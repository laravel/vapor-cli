<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;

class RunDeploymentHooksTimedOut
{
    /**
     * The deployment that have failed.
     *
     * @var \Laravel\VaporCli\Deployment
     */
    protected $deployment;

    /**
     * Create a new solution instance.
     *
     * @param  \Laravel\VaporCli\Deployment  $deployment
     * @return void
     */
    public function __construct($deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Checks if the solution is applicable.
     *
     * @return bool
     */
    public function applicable()
    {
        return Str::contains($this->deployment->status_message, [
            'App\Jobs\RunDeploymentHooks has been attempted too many times or run too long. The job may have previously timed out.',
        ]);
    }

    /**
     * Returns the list of solutions based on the deployment.
     *
     * @return array
     */
    public function all()
    {
        return [
            'Ensure your application is using the most recent versions of "laravel/vapor-cli" and "laravel/vapor-core".',
            'Review "CLI" environment logs: https://vapor.laravel.com/app/projects/'.$this->deployment->project_id.'/environments/'.$this->deployment->environment['name'].'/logs',
        ];
    }
}
