<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class ZoneListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('zone:list')
            ->setDescription('List the DNS zones that belong to the current team');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->table([
            'ID', 'Zone', 'Nameservers',
        ], collect($this->vapor->zones())->map(function ($zone) {
            return [
                $zone['id'],
                $zone['zone'],
                implode(PHP_EOL, $zone['nameservers']),
            ];
        })->all());
    }
}
