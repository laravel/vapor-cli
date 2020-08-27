<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class ProviderCommand extends Command
{
    use RetrievesProviderCredentials;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('provider')
            ->setDescription('Link a new cloud provider account to your user account');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $provider = 'AWS';

        $name = Helpers::ask('What should the cloud provider be named', 'Personal');

        $this->vapor->createProvider(
            $provider,
            $name,
            $this->{"get{$provider}Credentials"}()
        );

        Helpers::info('Cloud provider account linked successfully.');
    }
}
