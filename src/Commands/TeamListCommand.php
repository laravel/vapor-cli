<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class TeamListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('team:list')
            ->setDescription('List the teams that you belong to');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $user = $this->vapor->user();

        $allTeams = array_merge(
            $this->vapor->ownedTeams(),
            $this->vapor->teams()
        );

        $allTeams = collect($allTeams)->sortBy(function ($team) {
            return $team['name'];
        })->all();

        $this->table([
            'ID', 'Name', 'Owner',
        ], collect($allTeams)->map(function ($team) use ($user) {
            return [
                $team['id'],
                $team['name'],
                $team['owner']['id'] === $user['id']
                                ? 'You'
                                : $team['owner']['name'],
            ];
        })->all());
    }
}
