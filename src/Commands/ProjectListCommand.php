<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class ProjectListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('project:list')
            ->setDescription('List the projects that belong to the current team');
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
            'ID', 'Provider', 'Name', 'Region',
        ], collect($this->vapor->projects())->map(function ($project) {
            return [
                $project['id'],
                $project['cloud_provider']['name'],
                $project['name'],
                $project['region'],
            ];
        })->all());
    }
}
