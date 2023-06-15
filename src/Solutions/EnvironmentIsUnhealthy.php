<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;

class EnvironmentIsUnhealthy
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
            'Unable to obtain a healthy response from the environment being deployed.',
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
            'Review "CLI" environment logs: https://vapor.laravel.com/app/projects/'.$this->deployment->project_id.'/environments/'.$this->deployment->environment['name'].'/logs?period=5m&type=cli',
        ];
    }
}
