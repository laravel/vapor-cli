<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class JumpCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jump')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the jumpbox')
            ->setDescription('Create a new jumpbox for accessing private databases');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $networkId = $this->determineNetwork(
            'Which network should the jumpbox be placed in?'
        );

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        $response = $this->vapor->createJumpBox(
            $networkId,
            $this->argument('name')
        );

        $jumpBox = $response['jump_box'];

        Helpers::line('<info>Jump-box</info> <comment>['.$jumpBox['name'].']</comment> <info>creation initiated successfully.</info>');
        Helpers::line();
        Helpers::line('Jumpboxes may take several minutes to finish provisioning.');
        Helpers::line();
        Helpers::comment('Private Key:');
        Helpers::line();
        Helpers::line($response['private_key']);

        $this->storePrivateKey($jumpBox['name'], $response['private_key']);
    }

    /**
     * Store the private key for the jump-box if the user desires.
     *
     * @param  array  $jumpBox
     * @param  string  $privateKey
     * @return void
     */
    protected function storePrivateKey($name, $privateKey)
    {
        if (is_dir(Helpers::home().'/.ssh') &&
            Helpers::confirm('Would you like to store the private key in your ~/.ssh directory', true)) {
            file_put_contents($path = Helpers::home().'/.ssh/vapor-jump-'.$name, $privateKey);

            chmod($path, 0600);

            Helpers::line('<info>Private key written to:</info> '.$path);
        }
    }
}
