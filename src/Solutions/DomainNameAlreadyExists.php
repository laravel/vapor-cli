<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;
use Laravel\VaporCli\Deployment;

class DomainNameAlreadyExists
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
            'The domain name you provided already exists.',
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
            'Please ensure the domain is not in use on a different AWS account or on a different Vapor environment.',
        ];
    }
}
