<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;
use Laravel\VaporCli\Deployment;

class ResourceUpdateInProgress
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
     *
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
            'The operation cannot be performed at this time. An update is in progress for resource:',
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
            'AWS is running updates in your infrastructure. Please try again within a few minutes.',
        ];
    }
}
