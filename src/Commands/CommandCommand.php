<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('command')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('command', null, InputOption::VALUE_OPTIONAL, 'The command that should be executed')
            ->setDescription('Execute a CLI command');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        // First we will initiate the invocation on the service backend. Invocations run
        // in the background to avoid racking up compute time on the server side so I
        // don't have to pay for all invocations. We will then poll their statuses.
        $command = $this->vapor->invoke(
            Manifest::id(),
            $environment = $this->argument('environment'),
            $this->getCommand()
        );

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
     * Get the command to run.
     *
     * @return string
     */
    protected function getCommand()
    {
        return $this->option('command') ?? Helpers::ask('What command would you like to execute');
    }

    /**
     * Wait for the given command to finish executing.
     *
     * @param array $command
     *
     * @return array
     */
    protected function waitForCommandToFinish(array $command)
    {
        while ($command['status'] !== 'finished') {
            sleep(1);

            $command = $this->vapor->command($command['id']);
        }

        return $command;
    }

    /**
     * Display the status code of the command.
     *
     * @param array $command
     *
     * @return void
     */
    protected function displayStatusCode(array $command)
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
     * @param array $command
     *
     * @return void
     */
    protected function displayOutput(array $command)
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
     * @param array $command
     * @param int   $statusCode
     *
     * @return void
     */
    protected function displayLog(array $command, $statusCode)
    {
        if (! isset($command['output']) || $statusCode !== 0) {
            Helpers::line();
            Helpers::comment('Function Logs:');
            Helpers::line();

            Helpers::write(base64_decode($command['log']));
        }
    }
}
