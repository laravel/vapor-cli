<?php

namespace Laravel\VaporCli\Commands\Output;

use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;

class CommandResult
{
    /**
     * Render the output.
     *
     * @param  array  $output
     * @return void
     */
    public function render($command)
    {
        Helpers::step('<options=bold>Executing Function...</>'.PHP_EOL);

        // We will poll the backend service to get the invocations status and wait until
        // it gets done processing. Once it's done we will be able to show the status
        // of the invocation and any related logs which will need to get displayed.
        $command = $this->waitForCommandToFinish($command);

        $this->displayStatusCode($command);

        $this->displayOutput($command);

        Helpers::line();
        Helpers::line('<fg=magenta>Vapor Command ID:</> '.$command['id']);
        Helpers::line('<fg=magenta>AWS Request ID:</> '.$command['request_id']);
        Helpers::line('<fg=magenta>AWS Log Group Name:</> '.$command['log_group']);
        Helpers::line('<fg=magenta>AWS Log Stream Name:</> '.$command['log_stream']);
    }

    /**
     * Wait for the given command to finish executing.
     *
     * @param  array  $command
     * @return array
     */
    protected function waitForCommandToFinish($command)
    {
        while ($command['status'] !== 'finished') {
            sleep(1);

            $command = Helpers::app(ConsoleVaporClient::class)
                ->command($command['id']);
        }

        return $command;
    }

    /**
     * Display the status code of the command.
     *
     * @param  array  $command
     * @return void
     */
    protected function displayStatusCode($command)
    {
        if (! isset($command['status_code'])) {
            return;
        }

        $command['status_code'] === 0
                ? Helpers::line('<finished>Status Code:</> 0')
                : Helpers::line('<fg=red>Status Code:</> '.$command['status_code']);
    }

    /**
     * Display the output of the command.
     *
     * @param  array  $command
     * @return void
     */
    protected function displayOutput($command)
    {
        Helpers::line();

        if (isset($command['output'])) {
            Helpers::comment('Output:');

            $output = $command['output'];
            $output = base64_decode($output);

            if ($json = json_decode($output, true)) {
                $output = $json['output'];
            }

            Helpers::write($output);
        }
    }

    /**
     * Display the command's log messages.
     *
     * @param  array  $command
     * @param  int  $statusCode
     * @return void
     */
    protected function displayLog($command, $statusCode)
    {
        if (! isset($command['output']) || $statusCode !== 0) {
            Helpers::line();
            Helpers::comment('Function Logs:');
            Helpers::line();

            Helpers::write(base64_decode($command['log']));
        }
    }
}
