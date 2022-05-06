<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\DatabaseInstanceClasses;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DatabaseCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('database')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the database')
            ->addOption('public', null, InputOption::VALUE_NONE, 'Indicate that the database should be publicly accessible (with password)')
            ->addOption('serverless', null, InputOption::VALUE_NONE, 'Indicate that a serverless Aurora database should be created')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Create a small (db.t3.micro), public RDS instance')
            ->setDescription('Create a new database');
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
            'Which network should the database be placed in?'
        );

        if (is_null($networkId)) {
            Helpers::abort('Unable to find a network with that name / ID.');
        }

        $public = $this->determineIfPublic();

        if (! $public &&
            ! $this->networkHasNatGateway($networkId) &&
            ! Helpers::confirm('A private database will require Vapor to add a NAT gateway to your network (~32 / month). Would you like to proceed', true)) {
            Helpers::abort('Action cancelled.');
        }

        $instanceClass = $this->determineInstanceClass(
            $databaseType = $this->determineDatabaseType($public)
        );

        $allocatedStorage = $this->determineAllocatedStorage($databaseType);

        $pause = $databaseType == 'aurora-serverless' &&
                 Helpers::confirm('To reduce your bill, should the database pause during periods of inactivity', false);

        $response = $this->vapor->createDatabase(
            $networkId,
            $this->argument('name'),
            $databaseType,
            $instanceClass,
            $allocatedStorage,
            $public,
            $pause
        );

        Helpers::info('Database creation initiated successfully.');
        Helpers::line();
        Helpers::line('<comment>Username:</comment> '.$response['username']);
        Helpers::line('<comment>Password:</comment> '.$response['password']);
        Helpers::line();
        Helpers::line('Databases may take several minutes to finish provisioning.');
    }

    /**
     * Determine if the database should be public.
     *
     * @return bool
     */
    protected function determineIfPublic()
    {
        if ($this->option('serverless')) {
            return false;
        }

        if ($this->option('public') || $this->option('dev')) {
            return true;
        }

        return Helpers::confirm('Should the database be publicly accessible (with password)', false);
    }

    /**
     * Determine the database type.
     *
     * @param  bool  $public
     * @return string
     */
    protected function determineDatabaseType($public)
    {
        if ($this->option('dev')) {
            return 'rds';
        }

        return tap($this->option('serverless') ? 'aurora-serverless-v2' : $this->menu('Which type of database would you like to create?', [
            'rds'                     => 'Fixed Size MySQL Instance 8.0 (Free Tier Eligible)',
            'rds-mysql-5.7'           => 'Fixed Size MySQL Instance 5.7 (Free Tier Eligible)',
            'aurora-serverless'       => 'Serverless v1 MySQL 5.7 Aurora Cluster',
            'aurora-serverless-v2'    => 'Serverless v2 MySQL 8.0 Aurora Cluster',
            'rds-pgsql-13.4'          => 'Fixed Size PostgreSQL Instance 13.4',
            'rds-pgsql-11.10'         => 'Fixed Size PostgreSQL Instance 11.10',
            'aurora-serverless-pgsql' => 'Serverless PostgreSQL Aurora Cluster',
        ]), function ($type) use ($public) {
            if (in_array($type, ['aurora-serverless', 'aurora-serverless-v2', 'aurora-serverless-pgsql']) && $public) {
                Helpers::abort('Aurora Serverless clusters may not be publicly accessible.');
            }
        });
    }

    /**
     * Determine the instance class of the database.
     *
     * @param  string  $type
     * @return string|null
     */
    protected function determineInstanceClass($type)
    {
        if ($this->option('dev')) {
            return 'db.t3.micro';
        }

        if (in_array($type, ['aurora-serverless', 'aurora-serverless-v2'])) {
            return;
        }

        if ($type == 'rds'
            || $type == 'rds-mysql-5.7'
            || $type == 'rds-pgsql-11.10'
            || $type == 'rds-pgsql-13.4') {
            return $this->determineRdsInstanceClass();
        }
    }

    /**
     * Determine the instance class of an RDS database.
     *
     * @return string
     */
    protected function determineRdsInstanceClass()
    {
        $type = $this->menu('Which type of database instance would you like to create?', [
            'general' => 'General Purpose',
            'memory'  => 'Memory Optimized',
        ]);

        if ($type == 'general') {
            return $this->menu(
                'How much performance does your database require?',
                DatabaseInstanceClasses::general()
            );
        } else {
            return $this->menu(
                'How much performance does your database require?',
                DatabaseInstanceClasses::memory()
            );
        }
    }

    /**
     * Determine how much storage should be allocated to the database.
     *
     * @param  string  $type
     * @return int
     */
    protected function determineAllocatedStorage($type)
    {
        if (in_array($type, ['aurora-serverless', 'aurora-serverless-v2', 'aurora-serverless-pgsql'])) {
            return;
        }

        if ($this->option('dev')) {
            return 25;
        }

        $allocatedStorage = Helpers::ask('What is the maximum amount of storage that may be allocated to your database (between 25GB and 32768GB) ($0.115 / GB)', 100);

        if ($allocatedStorage < 25 || $allocatedStorage > 32768) {
            Helpers::abort('Maximum allocated storage must be between 25GB and 32TB.');
        }

        return $allocatedStorage;
    }

    /**
     * Determine if the given network has a NAT gateway.
     *
     * @param  int  $networkId
     * @return bool
     */
    protected function networkHasNatGateway($networkId)
    {
        return collect($this->vapor->networks())->first(function ($network) use ($networkId) {
            return $network['id'] == $networkId;
        })['has_internet_access'] ?? false;
    }
}
