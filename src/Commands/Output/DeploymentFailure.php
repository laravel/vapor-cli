<?php

namespace Laravel\VaporCli\Commands\Output;

use Laravel\VaporCli\Commands\HookOutputCommand;
use Laravel\VaporCli\ConsoleVaporClient;
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
        Helpers::line('');
        Helpers::danger('    <bg=red;options=bold> Deployment Failed </>');

        if ($deployment->status_message) {
            Helpers::line('');
            $message = $deployment->status_message;
            Helpers::line("    $message");
        }

        $deployment->solutions()->whenNotEmpty(function ($solutions) {
            Helpers::line('');
            Helpers::line('    <fg=white;options=bold>Possible solutions:</>');

            $solutions->each(function ($solution) {
                Helpers::line(sprintf(
                    '    <fg=blue;options=bold>✓ </>%s',
                    $solution
                ));
            });
        });

        Helpers::line('');

        if ($deployment->hasFailedHooks() && ($hook = collect($deployment->hooks)->where('status', 'failed')->first())) {
            $output = Helpers::app(ConsoleVaporClient::class)->deploymentHookOutput($hook['id'])['output'];

            Helpers::line("<fg=red>Hook:</> {$hook['command']}");
            HookOutputCommand::writeOutput($output);

            Helpers::line();
            Helpers::line('<fg=red>Logs:</> You may review its logs using the `hook:log '.$hook['id'].'` command.');
        }
    }
}
