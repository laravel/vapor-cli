<?php

namespace Laravel\VaporCli\Commands\Output;

use GuzzleHttp\Exception\RequestException;
use Laravel\VaporCli\Commands\HookLogCommand;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Models\Deployment;

class DeploymentFailure
{
    /**
     * Render the output.
     *
     * @param \Laravel\VaporCli\Models\Deployment $deployment
     *
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

        if ($deployment->hasFailedHooks() && ($failedHook = collect($deployment->hooks)->where('status', 'failed')->first())) {
            Helpers::line("<fg=red>Hook:</> {$failedHook['command']}");
            Helpers::line('<fg=yellow>Fetching logs...</>');
            sleep(5);
            $tries = 0;
            while ($tries < 3) {
                sleep(2);
                $tries++;

                try {
                    $hook = Helpers::app(ConsoleVaporClient::class)->deploymentHook($failedHook['id']);

                    if (isset($hook['log']) && !empty($hook['log'])) {
                        Helpers::line('<fg=red>Logs:</>');

                        return HookLogCommand::writeLog($hook['log']);
                    }
                } catch (RequestException $e) {
                    if ($e->getResponse()->getStatusCode() !== 422) {
                        throw $e;
                    }
                }
            }

            Helpers::line('<fg=red>Logs:</> You may review its logs using the `hook:log '.$hook['id'].'` command.');
        }
    }
}
