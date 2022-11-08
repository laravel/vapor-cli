<?php

namespace Laravel\VaporCli\Commands;

use GuzzleHttp\Exception\ClientException;
use Laravel\VaporCli\Config;
use Laravel\VaporCli\Exceptions\NeedsTwoFactorAuthenticationTokenException;
use Laravel\VaporCli\Helpers;
use Psr\Http\Message\ResponseInterface;

class LogoutCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('logout')
            ->setDescription('Disassociate your Laravel Vapor account');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $token = Helpers::config('token');
        if(empty($token)) {
            Helpers::abort("You're not logged in.");
            return;
        }

        Helpers::config(['token' => null]);

        Helpers::info('Logged Out.'.PHP_EOL);
    }
}
