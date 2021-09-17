<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CertDeleteCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cert:delete')
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain name')
            ->addOption('certificate', null, InputOption::VALUE_OPTIONAL, 'The certificate ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Perform the action without confirmation')
            ->setDescription('Delete a certificate');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        if ($this->option('certificate')) {
            if (! $this->option('force') &&
                ! Helpers::confirm('Are you sure you want to delete this certificate', false)) {
                Helpers::abort('Action cancelled.');
            }

            $id = $this->option('certificate');
        } else {
            $id = $this->determineCertificate();
        }

        $this->vapor->deleteCertificate($id);

        Helpers::info('Certificate deleted successfully.');
    }

    /**
     * Determine which certificate should be deleted.
     *
     * @return string
     */
    protected function determineCertificate()
    {
        $certificates = $this->vapor->certificates(
            $this->argument('domain')
        );

        if (empty($certificates)) {
            Helpers::abort('You do not have any certificates matching the given criteria.');
        }

        if ($this->argument('domain') && count($certificates) === 1) {
            return $this->getCertificateForDomain(
                $certificates,
                $this->argument('domain')
            );
        }

        return $this->chooseCertificate(
            'Which certificate would you like to delete?',
            $certificates
        );
    }

    /**
     * Get the certificate ID for the given domain.
     *
     * @param  array  $certificates
     * @param  string  $domain
     * @return string
     */
    protected function getCertificateForDomain(array $certificates, $domain)
    {
        $certificate = collect($certificates)->firstWhere(
            'domain',
            $domain
        );

        if (! $certificate) {
            Helpers::abort('You do not have any certificates matching the given criteria.');
        }

        return $certificate['id'];
    }
}
