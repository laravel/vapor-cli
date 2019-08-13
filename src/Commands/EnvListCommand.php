<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class EnvListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:list')
            ->setDescription('List the environments for the project');
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
            'ID', 'Name', 'URL',
        ], collect($this->vapor->environments(Manifest::id()))->map(function ($environment) {
            return [
                $environment['id'],
                $environment['name'],
                'https://'.$environment['vanity_domain'],
            ];
        })->all());
    }
}
