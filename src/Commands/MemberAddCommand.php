<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MemberAddCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('member:add')
            ->setAliases(['team:add', 'member:update'])
            ->addArgument('email', InputArgument::OPTIONAL, "The user's email address")
            ->addOption('permissions', null, InputOption::VALUE_OPTIONAL, "The user's permissions")
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Authorize the user to perform all operations')
            ->setDescription('Add a team member to your current team or update their permissions');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if ($this->option('permissions')) {
            $permissions = explode(',', $this->option('permissions'));
        } elseif ($this->option('admin')) {
            $permissions = ['*'];
        } else {
            $permissions = $this->vapor->defaultMemberPermissions();
        }

        $this->vapor->addTeamMember(
            $this->argument('email') ?? Helpers::ask("What is the user's email address"),
            $permissions
        );

        Helpers::info('Team updated successfully.');
        Helpers::line();
        Helpers::info('This team member has the following permissions:');
        Helpers::line();
        Helpers::line(implode(PHP_EOL, $permissions));
    }
}
