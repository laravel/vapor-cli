<?php

namespace Laravel\VaporCli\Commands\Output;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Models\Deployment;

class DeploymentFailure
{
    /**
     * Render the output.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @return void
     */
    public function render(Deployment $deployment)
    {
        Helpers::line();
        Helpers::line('<fg=red>An error occurred during deployment.</>');

        if ($deployment->status_message) {
            Helpers::line();
            Helpers::line("<fg=red>Message:</> {$deployment->status_message}");
        }

        if ($deployment->hasFailedHooks()) {
            Helpers::line();
            Helpers::danger('A deployment hook failed. You may review its logs using the hook:log command.');
        }
    }
}
