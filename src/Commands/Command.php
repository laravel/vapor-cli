<?php

namespace Laravel\VaporCli\Commands;

use DateTime;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    use ProvidesSelectionMenus;

    /**
     * The Vapor client instance.
     *
     * @var \Laravel\VaporCli\ConsoleVaporClient
     */
    public $vapor;

    /**
     * The input implementation.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    public $input;

    /**
     * The output implementation.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    public $output;

    /**
     * The DateTime representing the time the command started.
     *
     * @var \DateTime
     */
    protected $startedAt;

    /**
     * The number of rows in the last refreshed table.
     *
     * @var int
     */
    public $rowCount = 0;

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startedAt = new DateTime();

        $this->vapor = Helpers::app(ConsoleVaporClient::class);

        Helpers::app()->instance('input', $this->input = $input);
        Helpers::app()->instance('output', $this->output = $output);

        $this->configureManifestPath($input);
        $this->configureOutputStyles($output);

        return Helpers::app()->call([$this, 'handle']) ?: 0;
    }

    /**
     * Configure the manifest location.
     *
     * @param  \Symfony\Component\Console\InputInterface  $input
     * @return void
     */
    protected function configureManifestPath(InputInterface $input)
    {
        Helpers::app()->offsetSet('manifest', $input->getOption('manifest') ?? Path::defaultManifest());
    }

    /**
     * Configure the output styles for the application.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function configureOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            'finished',
            new OutputFormatterStyle('green', 'default', ['bold'])
        );
    }

    /**
     * Get an argument from the input list.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get an option from the input list.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Ensure the user intends to manipulate the production environment.
     *
     * @param  string  $environment
     * @param  bool  $force
     * @return void
     */
    protected function confirmIfProduction($environment, $force = null)
    {
        if (($this->input->hasOption('force') &&
            $this->option('force')) ||
            $environment !== 'production') {
            return;
        }

        if (! Helpers::confirm('You are manipulating the production environment. Are you sure you want to proceed', false)) {
            Helpers::abort('Action cancelled.');
        }
    }

    /**
     * Format input into a textual table.
     *
     * @param  array  $headers
     * @param  array  $rows
     * @param  string  $style
     * @return void
     */
    public function table(array $headers, array $rows, $style = 'borderless')
    {
        Helpers::table($headers, $rows, $style);
    }

    /**
     * Format input to textual table, remove the prior table.
     *
     * @param  array  $headers
     * @param  array  $rows
     * @return void
     */
    protected function refreshTable(array $headers, array $rows)
    {
        if ($this->rowCount > 0) {
            Helpers::write(str_repeat("\x1B[1A\x1B[2K", $this->rowCount + 4));
        }

        $this->rowCount = count($rows);

        $this->table($headers, $rows);
    }

    /**
     * Create a selection menu with the given choices.
     *
     * @param  string  $title
     * @param  array  $choices
     * @return mixed
     */
    public function menu($title, $choices)
    {
        return Helpers::menu($title, $choices);
    }

    /**
     * Get the ID of an item by name.
     *
     * @param  array  $items
     * @param  string  $name
     * @return int
     */
    protected function findIdByName(array $items, $name, $attribute = 'name')
    {
        return collect($items)->first(function ($item) use ($name, $attribute) {
            return $item[$attribute] == $name;
        })['id'] ?? null;
    }

    /**
     * Call another console command.
     *
     * @param  string  $command
     * @param  array  $arguments
     * @return int
     */
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            new ArrayInput($arguments),
            Helpers::app('output')
        );
    }
}
