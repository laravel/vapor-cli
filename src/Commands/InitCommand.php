<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Dockerfile;
use Laravel\VaporCli\GitIgnore;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;

class InitCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initialize a new project in the current directory');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->initializeAccount();

        // First we will determine which cloud provider account the project should get
        // created on. We will also determine the project's name. Once we get these
        // two pieces of information we will initialize this project's structure.
        $providers = $this->vapor->providers();

        $response = $this->vapor->createProject(
            $this->determineName(),
            $this->determineProvider('Which cloud provider should the project belong to?'),
            $this->determineRegion('Which region should the project be placed in?'),
            Helpers::confirm('Would you like Vapor to assign vanity domains to each of your environments?')
        );

        Manifest::fresh($response['project']);

        Dockerfile::fresh('staging');

        Dockerfile::fresh('production');

        // Finally we will add some files to the Gitignore file so that they don't get
        // placed in source control by accident. In particular, we need to ignore a
        // environment file for each environment. We will also ignore the builds.
        GitIgnore::add($this->filesToIgnore());

        Helpers::info(Helpers::exclaim().'! Your project has been initialized.');

        if (Helpers::confirm('Would you like to install the laravel/vapor-core package')) {
            passthru('composer require laravel/vapor-core --update-with-dependencies');
        }
    }

    /**
     * Initialize the account with a few basic entities if necessary.
     *
     * @return void
     */
    protected function initializeAccount()
    {
        if (empty($this->vapor->providers())) {
            $this->call('provider');
        }
    }

    /**
     * Determine the name of the project.
     *
     * @return string
     */
    protected function determineName()
    {
        return Str::slug(Helpers::ask(
            'What is the name of this project',
            basename(Path::current())
        ));
    }

    /**
     * Get the files that should be added to the Gitignore file.
     *
     * @return array
     */
    protected function filesToIgnore()
    {
        return [
            '.vapor/',
            '.env.production',
            '.env.staging',
        ];
    }
}
