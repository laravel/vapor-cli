<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class MemberListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('member:list')
            ->setDescription('List the members of the current team');
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
            'Name', 'Email Address',
        ], collect($this->vapor->teamMembers())->sortBy->name->map(function ($user) {
            return [
                $user['name'],
                $user['email'],
            ];
        })->all());
    }
}
