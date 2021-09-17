<?php

namespace Laravel\VaporCli\Commands;

use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TailCommand extends Command
{
    /**
     * The event IDs that we have seen while tailing.
     *
     * @var array
     */
    protected $seen = [];

    /**
     * The time (UNIX) of the last observed log message.
     *
     * @var int
     */
    protected $start;

    /**
     * The time the command was invoked.
     *
     * @var \DateTimeInterface
     */
    protected $invokedAt;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('tail')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'The text that should be used to filter the logs')
            ->addOption('cli', null, InputOption::VALUE_NONE, 'Tail the log for the CLI / queue function')
            ->addOption('without-queue', null, InputOption::VALUE_NONE, 'Hide Vapor generated queue processing messages')
            ->setDescription('Tail the log for an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->initializeTimestamps();

        while (true) {
            if ($this->shouldTimeout()) {
                return $this->timeout();
            }

            $nextToken = $this->displayLogMessages($nextToken ?? null);

            sleep(3);
        }
    }

    /**
     * Initialize the command's timestamps.
     *
     * @return void
     */
    protected function initializeTimestamps()
    {
        $this->start = time() * 1000;

        $this->invokedAt = Carbon::now();
    }

    /**
     * Detrmine if the tail command should timeout.
     *
     * @return bool
     */
    protected function shouldTimeout()
    {
        return Carbon::now()->subMinutes(30)->gte($this->invokedAt);
    }

    /**
     * Display the unseen log messages.
     *
     * @param  string|null  $nextToken
     * @return string
     */
    protected function displayLogMessages($nextToken)
    {
        $response = $this->vapor->tail(
            Manifest::id(),
            $this->argument('environment'),
            $this->option('cli'),
            $this->currentFilter(),
            $this->start,
            $nextToken ?? null
        );

        return tap($response['nextToken'] ?? null, function () use ($response) {
            foreach ($response['events'] as $event) {
                $this->displayEvent($event);

                $this->start = $event['timestamp'];
            }
        });
    }

    /**
     * Get the current filter string for the tail operation.
     *
     * @return string|null
     */
    protected function currentFilter()
    {
        if ($this->option('filter')) {
            return '"'.$this->option('filter').'"';
        }
    }

    /**
     * Display the given log event.
     *
     * @param  array  $event
     * @return void
     */
    protected function displayEvent(array $event)
    {
        if ($this->skippable($event)) {
            return;
        }

        $messages = trim(Str::after($event['message'], '[STDERR]'));

        foreach (explode("\n", $messages) as $message) {
            if ($this->currentFilter() &&
                ! Str::contains($message, trim($this->currentFilter(), '"'))) {
                continue;
            }

            $message = json_decode($message, true);

            if ($message && ! empty($message) && ! empty($message['level_name'])) {
                $this->displayMessage($message);
            }
        }
    }

    /**
     * Display the event message.
     *
     * @param  array  $message
     * @return void
     */
    protected function displayMessage(array $message)
    {
        $level = strtolower($this->formatLevelName($message['level_name']));

        $this->output->writeln(sprintf(
            '[<fg=magenta>%s</>] [<comment>%s</comment>]: %s',
            $this->formatDate($message['datetime']),
            $level,
            $this->formatMessage($message['message'])
        ));

        if (isset($message['context']) && ! empty($message['context'])) {
            $this->displayContext($message['context']);
        }
    }

    /**
     * Format the given level name for display.
     *
     * @param  string  $name
     * @return string
     */
    protected function formatLevelName($name)
    {
        switch ($name) {
            case 'DEBUG':
            case 'INFO':
                return '<info>'.$name.'</info>';
            case 'NOTICE':
            case 'WARNING':
                return '<comment>'.$name.'</comment>';
            default:
                return '<fg=red>'.$name.'</>';
        }
    }

    /**
     * Format the given log message date.
     *
     * @param  string|array  $date
     * @return string
     */
    public function formatDate($date)
    {
        if (is_array($date)) {
            return $date['date'];
        }

        return (new DateTime($date))->format('Y-m-d H:i:s');
    }

    /**
     * Format the log message.
     *
     * @param  string  $message
     * @return string
     */
    protected function formatMessage($message)
    {
        return str_replace(
            ['(Vapor)', '(Queue Processing)', '(Queue Processed)'],
            ['<comment>(Vapor)</comment>', '<fg=cyan>(Queue Processing)</>', '<fg=cyan>(Queue Processed)</>'],
            $message
        );
    }

    /**
     * Display the message context.
     *
     * @param  array  $context
     * @return void
     */
    protected function displayContext(array $context)
    {
        unset($context['aws_request_id']);

        if (empty($context)) {
            return;
        }

        $context = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $this->output->writeln(PHP_EOL.$context.PHP_EOL);
    }

    /**
     * Determine if the given message is skippable.
     *
     * @param  array  $event
     * @return bool
     */
    protected function skippable($event)
    {
        if ($this->skippableMessage($event['message']) ||
            isset($this->seen[$event['eventId']])) {
            return true;
        }

        $this->seen[$event['eventId']] = true;

        return false;
    }

    /**
     * Determine if the message content makes it skippable.
     *
     * @param  string  $message
     * @return bool
     */
    protected function skippableMessage($message)
    {
        return Str::startsWith($message, [
            'START RequestId',
            'END RequestId',
            'REPORT RequestId',
        ]) || ($this->option('without-queue') && Str::contains($message, [
            '(Queue Processing)',
            '(Queue Processed)',
        ]));
    }

    /**
     * Display the timeout message and exit.
     *
     * @return void
     */
    protected function timeout()
    {
        Helpers::abort('Tail command has timed out. If you need to keep tailing, please restart the command.');
    }
}
