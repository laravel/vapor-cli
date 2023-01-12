<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class ZoneDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('zone:delete')
            ->setAliases(['domain:delete'])
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain name / ID')
            ->setDescription('Delete a domain');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! is_numeric($zoneId = $this->argument('domain'))) {
            $zoneId = $this->findIdByName($this->vapor->zones(), $zoneId, 'zone');
        }

        if (is_null($zoneId)) {
            Helpers::abort('Unable to find a domain with that name / ID.');
        }

        $zone = $this->vapor->zone($zoneId);

        if (! Helpers::confirm("Are you sure you want to delete [{$zone['zone']}] from Vapor", false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->deleteZone($zoneId);

        Helpers::info('Domain successfully deleted from Vapor. Please note that the DNS zone will still persist on AWS.');
    }
}
