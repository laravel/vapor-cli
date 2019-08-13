<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Config;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Exceptions\NeedsTwoFactorAuthenticationTokenException;

class LoginCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('login')
            ->setDescription('Authenticate with Laravel Vapor');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $token = $this->vapor->login(
                $email = Helpers::ask('Email Address'),
                $password = Helpers::secret('Password')
            );
        } catch (NeedsTwoFactorAuthenticationTokenException $e) {
            $token = $this->vapor->login(
                $email,
                $password,
                $twoFactorAuthenticationToken = Helpers::secret('Two Factor Authentication Token')
            );
        }

        $token
            ? $this->store($token)
            : $this->displayFailureMessage();

        $this->ensureCurrentTeamIsSet();

        if (empty($providers = $this->vapor->providers()) &&
            Helpers::confirm('Would you like to link a cloud provider to your account', true)) {
            $this->call('provider');
        }
    }

    /**
     * Store the API token.
     *
     * @param  string  $token
     * @return void
     */
    protected function store($token)
    {
        Helpers::config(['token' => $token]);

        Helpers::info('Authenticated successfully.'.PHP_EOL);
    }

    /**
     * Display the authentication failure message.
     *
     * @return void
     */
    protected function displayFailureMessage()
    {
        Helpers::abort('Authentication failed.');
    }

    /**
     * Ensure the current team is set in the configuration file.
     *
     * @return void
     */
    protected function ensureCurrentTeamIsSet()
    {
        $teams = $this->vapor->ownedTeams();

        Config::set('team', collect($teams)->first(function ($team) {
            return $team['personal_team'] ?? false;
        })['id']);
    }
}
