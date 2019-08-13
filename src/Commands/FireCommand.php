<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class FireCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('fire')
            ->addArgument('email', InputArgument::OPTIONAL, "The user's email address")
            ->setDescription('Remove a team member from all of your teams');
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

        Helpers::info('User removed from all teams successfully.');
    }
}
