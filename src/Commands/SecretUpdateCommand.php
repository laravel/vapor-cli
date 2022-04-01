<?php

namespace Laravel\VaporCli\Commands;

use Exception;
use Illuminate\Support\Collection;
use Laravel\VaporCli\ApplicationFiles;
use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;


class SecretUpdateCommand extends Command
{
    private string $environment;
    private Collection $files;

    protected function configure(): void
    {
        $this
            ->setName('secret:update')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment for which to update the secrets')
            ->setDescription('Update the secrets in a Vapor environment');
    }

    public function handle(): int
    {
        $this->environment = $this->argument('environment');

        if ($this->environment === 'production') {
            if (!Helpers::confirm('We are running on production, are you sure you want to update all the secrets?', false)) {
                Helpers::abort('Not updating secrets');
            }
        }

        $this->prepareUploadingSecrets();

        $this->updateSecrets();

        Helpers::comment('You should deploy the project to ensure the new secrets are available.');
        Helpers::info(Helpers::exclaim());

        return Command::SUCCESS;
    }

    private function prepareUploadingSecrets(): void
    {
        try {
            Helpers::ensure_api_token_is_available();
        } catch (Exception $e) {
            Helpers::abort($e->getMessage());
        }

        if (!file_exists($path = Path::current() . "/secrets/$this->environment")) {
            Helpers::abort("The path {$path} doesn't exist");
        }

        $this->files = collect(ApplicationFiles::get($path));

        if ($this->files->isEmpty()) {
            Helpers::abort('There are no secrets present for ' . $this->environment . PHP_EOL);
        }
    }

    private function updateSecrets(): void
    {
        Helpers::info('Update secrets in Vapor' . PHP_EOL);

        $myFiles = $this->files;
        foreach ($myFiles as $file){

            $this->vapor->storeSecret(
                Manifest::id(),
                $this->environment,
                $file->getFilenameWithoutExtension(),
                $file->getContents()
            );

            sleep(1);
        }

        Helpers::line('Secrets stored successfully.' . PHP_EOL);
    }
}
