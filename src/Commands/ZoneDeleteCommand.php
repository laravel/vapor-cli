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
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name / ID')
            ->setDescription('Delete a DNS zone');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $zone = $this->argument('zone');

        if (! is_numeric($zoneId = $this->argument('zone'))) {
            $zoneId = $this->findIdByName($this->vapor->zones(), $zoneId, 'zone');
        }

        if (is_null($zoneId)) {
            Helpers::abort('Unable to find a zone with that name / ID.');
        }

        if (! Helpers::confirm("Are you sure you want to delete the zone for {$zone}", false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->deleteZone($zoneId);

        Helpers::info('Zone deleted successfully.');
    }
}
