<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class OpenCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('open')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->setDescription('Open an environment in your default browser');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $environment = $this->vapor->environmentNamed(
            Manifest::id(),
            $this->argument('environment')
        );

        if (empty($environment)) {
            Helpers::abort(sprintf(
                'Environment [%s] not found.',
                $this->argument('environment')
            ));
        }

        $domain = ! empty($environment['latest_deployment']['root_domains'])
            ? $environment['latest_deployment']['root_domains'][0]
            : $environment['vanity_domain'];

        if (empty($domain)) {
            Helpers::abort(sprintf(
                'No domain assigned to [%s] environment.',
                $this->argument('environment')
            ));
        }

        passthru(sprintf('open https://%s', $domain));
    }
}
