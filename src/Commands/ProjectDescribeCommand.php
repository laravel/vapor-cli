<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Path;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

class ProjectDescribeCommand extends Command
{
    const DEFAULT_FORMAT = '[<comment>%setting-key%</comment>] <info>%setting-value%</info>';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('project:describe')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List settings')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'List format')
            ->addArgument('setting-key', null, 'Setting key')
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

        $settingKey = $this->argument('setting-key');

        if ($settingKey && $settingKey === 'id') {
            // If we want the ID, we can just get it.
            Helpers::line(Manifest::id());

            return;
        }

        $project = $this->vapor->project(Manifest::id());

        $description = [
            'id' => $project['id'],
            'name' => $project['name'],
            'team_id' => $project['team_id'],
            'team_name' => $project['team']['name'],
            'region' => $project['region'],
            'github_repository' => $project['github_repository'],
            'management_url' => 'https://vapor.laravel.com/app/projects/' . $project['id'],
        ];

        // List the configuration of the file settings
        if ($this->option('list')) {
            $format = $this->option('format') ?: static::DEFAULT_FORMAT;

            foreach ($description as $settingKey => $settingValue) {
                if (is_bool($settingValue)) {
                    $settingValue = var_export($settingValue, true);
                }

                Helpers::line(str_replace(['%setting-key%', '%setting-value%'], [$settingKey, $settingValue], $format));
            }

            return;
        }

        if (!$settingKey || !is_string($settingKey)) {
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
