<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;
use Laravel\VaporCli\Deployment;

class EnvironmentHasExceededLimit
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
            'Lambda was unable to configure your environment variables because the environment variables you have provided exceeded the 4KB limit',
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
            'You can use encrypted environment files in place of or in addition to environment variables: https://docs.vapor.build/1.0/projects/environments.html#encrypted-environment-files',
            'You can also use the "Secrets" feature in conjunction with "Keys": https://vapor.laravel.com/app/projects/'.$this->deployment->project_id.'/environments/'.$this->deployment->environment['name'].'/secrets',
        ];
    }
}
