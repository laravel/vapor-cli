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
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name')
            ->setDescription('Create a new DNS zone');
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
            $this->determineProvider('Which cloud provider should the zone belong to?'),
            $this->argument('zone')
        );

        Helpers::info('Zone created successfully.');
        Helpers::line();
        Helpers::info('Nameservers:');
        Helpers::line();
        Helpers::line(implode(PHP_EOL, $zone['nameservers']));
    }
}
