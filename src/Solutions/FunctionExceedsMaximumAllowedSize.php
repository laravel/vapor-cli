<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;
use Laravel\VaporCli\Deployment;

class FunctionExceedsMaximumAllowedSize
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
            'Function code combined with layers exceeds the maximum allowed size',
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
            'AWS Lambda has strict limitations on the size of applications running within the environment.'
            .' If your application exceeds this limit, you may take advantage of Vapor\'s Docker based deployments.'
            .' Docker based deployments allow you to package and deploy applications up to 10GB in size.'
            .' When migrating an existing environment to a Docker runtime, please keep in mind that you won\'t be able to revert that environment to the default Vapor Lambda runtime later.'
            .' For that reason, you may want to create an environment for testing the Docker runtime first.'
            .' https://docs.vapor.build/1.0/projects/environments.html#building-custom-docker-images',
        ];
    }
}
