<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

class ProjectDescribeCommand extends Command
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
            ->setName('project:describe')
            ->addArgument('attribute', null, 'The project attribute you would like to retrieve')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Indicate that all attributes should be listed')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The list format string')
            ->setDescription('Describe the project');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $settingKey = $this->argument('attribute');

        if ($settingKey && $settingKey === 'id') {
            // If we want the ID, we can just get it.
            Helpers::line(Manifest::id());

            return;
        }

        $project = $this->vapor->project(Manifest::id());

        $description = [
            'id'                => $project['id'],
            'name'              => $project['name'],
            'team_id'           => $project['team_id'],
            'team_name'         => $project['team']['name'],
            'region'            => $project['region'],
            'github_repository' => $project['github_repository'],
            'management_url'    => 'https://vapor.laravel.com/app/projects/'.$project['id'],
        ];

        if ($this->option('list')) {
            $format = $this->option('format') ?: static::DEFAULT_FORMAT;

            foreach ($description as $settingKey => $settingValue) {
                if (is_bool($settingValue)) {
                    $settingValue = var_export($settingValue, true);
                }

                Helpers::line(str_replace(
                    ['%attribute-key%', '%attribute-value%'],
                    [$settingKey, $settingValue],
                    $format
                ));
            }

            return;
        }

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
