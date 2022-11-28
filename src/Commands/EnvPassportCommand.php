<?php

namespace Laravel\VaporCli\Commands;

use Dotenv\Dotenv;
use Dotenv\Exception\ExceptionInterface;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class EnvPassportCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:passport')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->setDescription('Store the application\'s Passport keys in the given environment file');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! Helpers::confirm("Passport keys are too large to be stored as AWS Lambda environment variables. You should only use this command when using Laravel's encrypted environment files. Would you like to proceed?", false)) {
            return static::FAILURE;
        }

        $environment = $this->argument('environment');
        $environmentFile = '.env.'.$environment;

        if (! file_exists(getcwd().'/'.$environmentFile)) {
            Helpers::abort('Unable to find .env file for environment: '.$environment);
        }

        if (! file_exists(getcwd().'/storage/oauth-private.key') ||
            ! file_exists(getcwd().'/storage/oauth-public.key')) {
            Helpers::abort('Unable to find Passport keys in [storage] directory.');
        }

        $contents = file_get_contents(getcwd().'/'.$environmentFile);

        try {
            $variables = Dotenv::parse($contents);
        } catch (ExceptionInterface $e) {
            Helpers::abort('Unable to parse environment file: '.$environmentFile);
        }

        if (array_key_exists('PASSPORT_PRIVATE_KEY', $variables) || array_key_exists('PASSPORT_PRIVATE_KEY', $variables)) {
            Helpers::abort('The environment file already contains Passport keys.');
        }

        $contents .= PHP_EOL.'PASSPORT_PRIVATE_KEY="'.file_get_contents(getcwd().'/storage/oauth-private.key').'"';
        $contents .= PHP_EOL.'PASSPORT_PUBLIC_KEY="'.file_get_contents(getcwd().'/storage/oauth-public.key').'"';

        file_put_contents(getcwd().'/'.$environmentFile, $contents);

        Helpers::info('Keys successfully added to '.$environmentFile.'.');
        Helpers::line('You should now encrypt the environment file using the "env:encrypt" command before redeploying your application.');
    }
}
