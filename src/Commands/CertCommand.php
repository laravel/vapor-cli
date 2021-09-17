<?php

namespace Laravel\VaporCli\Commands;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CertCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cert')
            // ->addArgument('validation-method', InputArgument::REQUIRED, 'The certificate validation method (email, dns)')
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain name')
            // ->addOption('add', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The additional domain names that should be added to the certificate', [])
            ->addOption('provider', null, InputOption::VALUE_OPTIONAL, 'The cloud provider ID')
            ->setDescription('Request a new SSL certificate');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $domain = $this->argument('domain');

        // $additionalDomains = $this->option('add');
        $additionalDomains = [];

        if (count(explode('.', $domain)) == 2 ||
            (count(explode('.', $domain)) === 3 &&
             Str::endsWith($domain, static::multiPartDomainEndings()))) {
            $additionalDomains = array_unique(
                array_merge($additionalDomains, ['*.'.$domain])
            );
        }

        $this->vapor->requestCertificate(
            $this->determineProvider('Which cloud provider should the certificate belong to?'),
            $domain,
            $additionalDomains,
            $this->determineRegion('Which region should the certificate be placed in?'),
            'dns'
            // $this->argument('validation-method')
        );

        Helpers::info('Certificate requested successfully for domains:');
        Helpers::line();

        $this->displayDomains($domain, $additionalDomains);

        Helpers::line();

        // if ($this->argument('validation-method') == 'dns') {
        if (true) {
            Helpers::line('Vapor will automatically add the DNS validation records to your zone.');
            Helpers::line('If you are using an outside DNS provider, you may retrieve the necessary CNAME records from the Vapor UI.');
        } else {
            Helpers::line('You will receive a domain verification email at the following email addresses:');
            Helpers::line();

            $this->displayEmailAddresses($domain);

            Helpers::line();
            Helpers::line('Please approve the certificate by following the directions in the verification email.');
        }
    }

    /**
     * Display the certificate domains.
     *
     * @param  string  $domain
     * @param  array  $additionalDomains
     * @return void
     */
    protected function displayDomains($domain, array $additionalDomains)
    {
        foreach (array_merge([$domain], $additionalDomains) as $requested) {
            Helpers::comment(' - '.$requested);
        }
    }

    /**
     * Display the certificate verification email addresses.
     *
     * @param  string  $domain
     * @return void
     */
    protected function displayEmailAddresses($domain)
    {
        foreach (['administrator', 'hostmaster', 'postmaster', 'webmaster', 'admin'] as $address) {
            Helpers::comment(' - '.$address.'@'.$domain);
        }
    }

    /**
     * Get all of the valid multi-segment domain endings.
     *
     * @return array
     */
    protected static function multiPartDomainEndings()
    {
        return [
            'co.nz',
            'co.uk',
            'co.za',
            'com.ar',
            'com.au',
            'com.br',
            'com.mx',
            'com.sg',
            'me.uk',
            'org.br',
            'org.nz',
            'org.uk',
        ];
    }
}
