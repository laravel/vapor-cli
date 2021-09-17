<?php

namespace Laravel\VaporCli\Solutions;

use Illuminate\Support\Str;
use Laravel\VaporCli\Deployment;

class BucketNameAlreadyExists
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
            'This bucket name may already be reserved by another AWS account.',
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
            'Amazon S3 bucket names must be globally unique across all AWS users. You must use a different storage bucket name.',
        ];
    }
}
