<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Config;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputOption;

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
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Team Name to switch to')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Team ID to switch to')
            ->setDescription('Switch to a different team context, you may optionally pass Team ID or Team Name');
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

        if( ! empty($this->option('name')) &&  ! empty($this->option('id')))
        {
            Helpers::abort('Use either Team Name or Team ID to switch your team.');
        }

        if( ! empty($this->option('name')) ||  ! empty($this->option('id')))
        {
            $searchBy = ! empty($this->option('name')) ? 'name' : 'id';

            $team = collect($allTeams)->where($searchBy, $this->option($searchBy))->first();

            if(empty($team))
            {
                Helpers::abort('Team not found.');
            }

            $teamId = $team['id'];
        }

        if( ! isset($teamId))
        {
            $teamId = $this->menu(
                'Which team would you like to switch to?',
                collect($allTeams)->sortBy->name->mapWithKeys(function ($team) {
                    return [$team['id'] => $team['name']];
                })->all()
            );
        }

        $this->vapor->switchCurrentTeam($teamId);

        Config::set('team', $teamId);

        Helpers::info('Current team context changed successfully.');
    }
}
