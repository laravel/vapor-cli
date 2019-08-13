<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Config;
use Laravel\VaporCli\Helpers;

class TeamSwitchCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('team:switch')
            ->setAliases(['switch'])
            ->setDescription('Switch to a different team context');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $allTeams = array_merge(
            $this->vapor->ownedTeams(),
            $this->vapor->teams()
        );

        $teamId = $this->menu(
            'Which team would you like to switch to?',
            collect($allTeams)->sortBy->name->mapWithKeys(function ($team) {
                return [$team['id'] => $team['name']];
            })->all()
        );

        $this->vapor->switchCurrentTeam($teamId);

        Config::set('team', $teamId);

        Helpers::info('Current team context changed successfully.');
    }
}
