<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class DatabaseTunnelCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database:tunnel')
            ->addArgument('database', InputArgument::REQUIRED, 'The name of the database')
            ->addArgument('port', InputArgument::OPTIONAL, 'The local port to serve connections on')
            ->setDescription('Create a secure tunnel to a database, allowing local connections');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $databases = $this->vapor->databases();

        if (! is_numeric($databaseId = $this->argument('database'))) {
            $databaseId = $this->findIdByName($databases, $databaseId);
        }

        if (is_null($databaseId)) {
            Helpers::abort('Unable to find a database with that name / ID.');
        }

        $jumpBox = $this->findCompatibleJumpBox(
            $database = collect($databases)->firstWhere('id', $databaseId)
        );

        $localPort = $this->argument('port') ?? ($database['port'] - 1);

        Helpers::line('<info>Establishing secure tunnel to</info> <comment>['.$database['name'].']</comment> <info>on</info> <comment>[localhost:'.$localPort.']</comment><info>...</info>');

        passthru(sprintf(
            'ssh ec2-user@%s -i %s -o LogLevel=error -L %d:%s:%d -N',
            $jumpBox['endpoint'],
            $this->storeJumpBoxKey($jumpBox),
            $localPort,
            $database['endpoint'],
            $database['port']
        ));
    }

    /**
     * Find a jump-box compatible with the database.
     *
     * @param  array  $database
     * @return array
     */
    protected function findCompatibleJumpBox(array $database)
    {
        $jumpBoxes = collect($this->vapor->jumpBoxes())->filter(function ($jumpBox) use ($database) {
            return $jumpBox['network_id'] == $database['network_id'];
        });

        $jumpBox = in_array($database['type'], ['rds', 'aurora-serverless', 'aurora-serverless-v2'])
            ? $jumpBoxes->first()
            : $jumpBoxes->firstWhere('version', '>', 1);

        if (is_null($jumpBox)) {
            Helpers::abort('A compatible jumpbox is required in order to create a tunnel.');
        }

        return $jumpBox;
    }

    /**
     * Store the private SSH key for the jump-box.
     *
     * @param  array  $jumpBox
     * @return string
     */
    protected function storeJumpBoxKey(array $jumpBox)
    {
        file_put_contents(
            $path = Helpers::home().'/.ssh/vapor-database-shell',
            $this->vapor->jumpBoxKey($jumpBox['id'])['private_key']
        );

        chmod($path, 0600);

        return $path;
    }
}
