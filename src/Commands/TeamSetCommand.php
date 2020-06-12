<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Config;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TeamSetCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('team:set')
            ->addArgument('team', InputArgument::REQUIRED, 'The team ID/Name to set')
            ->addOption('use-name', null, InputOption::VALUE_NONE, 'Use team Name instead of ID')
            ->setDescription('Manually set a team using ID or Name');
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

        $teams = collect($allTeams)->where($this->option('use-name') ? 'name' : 'id', $this->argument('team'));

        if($teams->count() < 1)
        {
            Helpers::abort('No team found.');
        }

        if($teams->count() > 1)
        {
            Helpers::abort('More than one team found with criteria.');
        }

        $team = $teams->first();

        $this->vapor->switchCurrentTeam($team['id']);

        Config::set('team', $team['id']);

        Helpers::line('<info>Current team context set to</info> <comment>['.$team['name'].']</comment>');
    }
}
