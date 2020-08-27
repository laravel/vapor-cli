<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class CertValidateCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cert:validate')
            ->setDescription('Resend the validation email for a certificate');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $certificates = $this->vapor->pendingCertificates();

        if (empty($certificates)) {
            Helpers::abort('There are no certificates currently pending validation.');
        }

        $this->vapor->resendCertificateValidationEmail(
            $this->chooseCertificate(
                'Which certificate are you attempting to validate?',
                $certificates
            )
        );

        Helpers::info('Validation email requested successfully.');
        Helpers::line();
        Helpers::line('You will receive a domain verification email within the next few minutes.');
        Helpers::line('Please approve the certificate by following the directions in the verification email.');
    }
}
