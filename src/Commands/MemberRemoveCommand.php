<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class MemberRemoveCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('member:remove')
            ->setAliases(['team:remove'])
            ->addArgument('email', InputArgument::OPTIONAL, "The user's email address")
            ->setDescription('Remove a team member from your current team');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->removeTeamMember(
            $this->argument('email') ?? Helpers::ask("What is the user's email address")
        );

        Helpers::info('Team member removed successfully.');
    }
}
