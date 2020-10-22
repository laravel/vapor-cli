<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class ProviderUpdateCommand extends Command
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
            ->setName('provider:update')
            ->addArgument('provider', InputArgument::REQUIRED, 'The cloud provider name / ID')
            ->setDescription('Update a cloud provider that is linked to your account');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $providers = $this->vapor->providers();

        if (! is_numeric($providerId = $this->argument('provider'))) {
            $providerId = $this->findIdByName($providers, $providerId);
        }

        $provider = collect($providers)->first(function ($provider) use ($providerId) {
            return $provider['id'] == $providerId;
        });

        if (is_null($provider)) {
            Helpers::abort('Unable to find a cloud provider with that name / ID.');
        }

        $name = Helpers::ask('What should the cloud provider be named', $provider['name']);

        $this->vapor->updateProvider(
            $provider['id'],
            $name,
            $this->{"get{$provider['type']}Credentials"}()
        );

        Helpers::info('Cloud provider account updated successfully.');
    }
}
