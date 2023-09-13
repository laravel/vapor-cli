<?php

namespace Laravel\VaporCli\Commands;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

class TestCommand extends Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ignoreValidationErrors();
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('test')
            ->addOption('php', null, InputOption::VALUE_OPTIONAL, 'The PHP version that should be used to execute the tests')
            ->setDescription('Run PHPUnit or Pest inside a simulated Vapor environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle()
    {
        array_splice($_SERVER['argv'], 2, 0, $this->getTestRunnerBinary());

        $this->getApplication()->find('local')->run(new ArrayInput([
            '--php' => $this->option('php'),
        ]), $this->output);
    }

    /**
     * Returns the test runner binary.
     *
     * @return string
     */
    protected function getTestRunnerBinary()
    {
        return is_executable('vendor/bin/pest') ? 'vendor/bin/pest' : 'vendor/bin/phpunit';
    }
}
