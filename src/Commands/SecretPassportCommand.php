<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class SecretPassportCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('secret:passport')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription('Store the application\'s Passport keys as secrets');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if (! file_exists(getcwd().'/storage/oauth-private.key') ||
            ! file_exists(getcwd().'/storage/oauth-public.key')) {
            Helpers::abort('Unable to find Passport keys in [storage] directory.');
        }

        $this->storePrivateKey();
        $this->storePublicKey();

        Helpers::info('Keys stored successfully as secrets.');
        Helpers::line('You should deploy the project to ensure the keys are available.');
    }

    /**
     * Store the Passport private key.
     *
     * @return void
     */
    protected function storePrivateKey()
    {
        $this->vapor->storeSecret(
            Manifest::id(),
            $this->argument('environment'),
            'PASSPORT_PRIVATE_KEY',
            file_get_contents(getcwd().'/storage/oauth-private.key')
        );
    }

    /**
     * Store the Passport public key.
     *
     * @return void
     */
    protected function storePublicKey()
    {
        $this->vapor->storeSecret(
            Manifest::id(),
            $this->argument('environment'),
            'PASSPORT_PUBLIC_KEY',
            file_get_contents(getcwd().'/storage/oauth-public.key')
        );
    }
}
