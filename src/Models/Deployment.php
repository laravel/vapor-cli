<?php

namespace Laravel\VaporCli\Models;

use Illuminate\Support\Str;
use Laravel\VaporCli\Solutions;

class Deployment
{
    /**
     * The deployment data.
     *
     * @var array
     */
    public $deployment;

    /**
     * Create a new model instance.
     *
     * @param  array  $deployment
     * @return void
     */
    public function __construct(array $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Get the names of the displayable steps.
     *
     * @param  array  $displayedSteps
     * @return array
     */
    public function displayableSteps(array $displayedSteps = [])
    {
        return collect($this->steps)
                ->filter(function ($step) {
                    return $step['status'] !== 'pending' &&
                           $step['status'] !== 'cancelled';
                })->map(function ($step) {
                    return $this->formatDeploymentStepName($step['name']);
                })->filter(function ($step) use ($displayedSteps) {
                    return ! in_array($step, $displayedSteps);
                })->all();
    }

    /**
     * Determine if the given deployment step should be displayed.
     *
     * @param  array  $step
     * @return bool
     */
    protected function stepShouldBeDisplayed(array $step)
    {
        return $step['status'] !== 'pending' &&
               ! in_array($step['name'], $this->displayedSteps);
    }

    /**
     * Format the deployment step name into a displayable value.
     *
     * @param  string  $name
     * @return string
     */
    protected function formatDeploymentStepName($name)
    {
        return str_replace(
            ['Iam', 'Api', 'Dns', 'Ensure', 'Update', 'Run'],
            ['IAM', 'API', 'DNS', 'Ensuring', 'Updating', 'Running'],
            ucwords(Str::snake($name, ' '))
        );
    }

    /**
     * Determine if the deployment has target domains.
     *
     * @return bool
     */
    public function hasTargetDomains()
    {
        return isset($this->deployment['target_domains']) &&
               ! empty($this->deployment['target_domains']);
    }

    /**
     * Determine if the deployment has any failed deployment hooks.
     *
     * @return bool
     */
    public function hasFailedHooks()
    {
        return (collect($this->steps)->first(function ($step) {
            return $step['name'] == 'RunDeploymentHooks';
        })['status'] ?? null) === 'failed';
    }

    /**
     * Get the vanity domain for the deployment environment.
     *
     * @return string
     */
    public function vanityDomain()
    {
        return $this->deployment['environment']['vanity_domain'];
    }

    /**
     * Returns a list of solutions for the current deployment failure.
     *
     * @return \Illuminate\Support\Collection
     */
    public function solutions()
    {
        return collect([
            Solutions\BucketNameAlreadyExists::class,
            Solutions\DomainNameAlreadyExists::class,
            Solutions\FunctionExceedsMaximumAllowedSize::class,
            Solutions\ResourceUpdateInProgress::class,
            Solutions\RunDeploymentHooksTimedOut::class,
        ])->map(function ($solutionsClass) {
            return new $solutionsClass($this);
        })->filter
        ->applicable()
        ->map
        ->all()
        ->flatten();
    }

    /**
     * Determine if the deployment is finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->status == 'finished';
    }

    /**
     * Determine if the deployment has ended.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return $this->has_ended;
    }

    /**
     * Get an item from the deployment data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->deployment[$key];
    }

    /**
     * Convert the model into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->deployment;
    }
}
