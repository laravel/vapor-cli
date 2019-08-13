<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class TeamCurrentCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('team:current')
            ->setDescription('Determine your current team context');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $team = collect(array_merge(
            $this->vapor->teams(),
            $this->vapor->ownedTeams()
        ))->where('id', Helpers::config('team'))->first();

        if (! $team) {
            Helpers::abort('Unable to determine current team.');
        }

        Helpers::line('<info>You are currently within the</info> <comment>['.$team['name'].']</comment> <info>team context.</info>');
    }
}
