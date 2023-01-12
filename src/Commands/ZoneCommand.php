<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class ZoneCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('zone')
            ->setAliases(['domain'])
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain name')
            ->setDescription('Add a domain');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $zone = $this->vapor->createZone(
            $this->determineProvider('Which cloud provider should the domain belong to?'),
            $this->argument('domain')
        );

        Helpers::info('Domain added successfully.');
        Helpers::line();
        Helpers::info('Nameservers:');
        Helpers::line();
        Helpers::line(implode(PHP_EOL, $zone['nameservers']));
    }
}
