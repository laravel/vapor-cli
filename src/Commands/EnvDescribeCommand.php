<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EnvDescribeCommand extends Command
{
    const DEFAULT_FORMAT = '[<comment>%attribute-key%</comment>] <info>%attribute-value%</info>';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('env:describe')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->addArgument('attribute', null, 'The environment attribute you would like to retrieve')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Indicate that all attributes should be listed')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The list format string')
            ->setDescription('Describe an environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $environment = $this->vapor->environmentNamed(
            Manifest::id(),
            $this->argument('environment')
        );

        $domains = [];

        if ($environment['latest_deployment']) {
            $domains = $environment['latest_deployment']['root_domains'] ?: [];
        }

        $domain = count($domains) ? $domains[0] : null;

        $description = [
            'project_id'               => $environment['project_id'],
            'uuid'                     => $environment['uuid'],
            'id'                       => $environment['id'],
            'name'                     => $environment['name'],
            'vanity_domain'            => $environment['vanity_domain'],
            'latest_deployment_id'     => $environment['latest_deployment_id'],
            'latest_deployment_status' => $environment['latest_deployment'] ? $environment['latest_deployment']['status'] : null,
            'latest_deployment_url'    => 'https://vapor.laravel.com/app/projects/'.$environment['project_id'].'/environments/'.$environment['name'].'/deployments/'.$environment['latest_deployment_id'],
            'deployment_status'        => $environment['deployment_status'],
            'domains'                  => $domains,
            'domain'                   => $domain,
            'management_url'           => 'https://vapor.laravel.com/app/projects/'.$environment['project_id'].'/environments/'.$environment['name'],
            'vanity_url'               => 'https://'.$environment['vanity_domain'],
            'custom_url'               => $domain ? 'https://'.$domain : null,
        ];

        if ($this->option('list')) {
            $format = $this->option('format') ?: static::DEFAULT_FORMAT;

            foreach ($description as $settingKey => $settingValue) {
                if (is_bool($settingValue)) {
                    $settingValue = var_export($settingValue, true);
                }

                if (is_array($settingValue)) {
                    $settingValue = implode(',', $settingValue);
                }

                Helpers::line(str_replace(
                    ['%attribute-key%', '%attribute-value%'],
                    [$settingKey, $settingValue],
                    $format
                ));
            }

            return;
        }

        $settingKey = $this->argument('attribute');

        if (! $settingKey || ! is_string($settingKey)) {
            return;
        }

        if (! array_key_exists($settingKey, $description)) {
            throw new RuntimeException($settingKey.' is not defined');
        }

        $settingValue = $description[$settingKey];

        if (is_bool($settingValue)) {
            $settingValue = var_export($settingValue, true);
        }

        Helpers::line($settingValue);
    }
}
