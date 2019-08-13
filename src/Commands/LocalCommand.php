<?php

namespace Laravel\VaporCli\Commands;

use Exception;
use Laravel\VaporCli\Path;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputOption;

class LocalCommand extends Command
{
    /**
     * The Docker for various PHP versions.
     *
     * @var array
     */
    public static $images = [
        '7.3' => 'laravelphp/vapor:php73',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ignoreValidationErrors();
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('local')
            ->addOption('php', null, InputOption::VALUE_OPTIONAL, 'The PHP version that should be used to execute the command')
            ->setDescription('Run a command inside a simulated Vapor environment');
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $options = array_slice($_SERVER['argv'], $this->option('php') ? 3 : 2);

        file_put_contents(
            $dockerComposePath = Path::current().'/vapor-docker-compose.yml',
            Yaml::dump($this->dockerConfiguration(), $inline = 20, $spaces = 4)
        );

        passthru(
            implode(' ', array_merge([
                'docker-compose',
                '-f',
                $dockerComposePath,
                'run',
                '--rm',
                '-e DB_HOST=mysql',
                '-e DB_DATABASE=vapor',
                '-e DB_PORT=3306',
                '-e DB_USERNAME=vapor',
                '-e DB_PASSWORD=secret',
                '-e REDIS_HOST=redis',
                '-v',
                Path::current().':/app',
                'app',
            ], $options))
        );

        unlink($dockerComposePath);
    }

    /**
     * Get the Docker configuration.
     *
     * @return array
     *
     * @throws Exception
     */
    protected function dockerConfiguration()
    {
        if ($this->option('php') && ! isset(static::$images[$this->option('php')])) {
            Helpers::abort('Invalid PHP version.');
        }

        return [
            'version' => '3.7',
            'services' => [
                'redis' => [
                    'image' => 'redis:alpine',
                    'volumes' => [
                        'vapor_redis:/data',
                    ],
                    'restart' => 'always',
                ],
                'mysql' => [
                    'image' => 'mysql:5.7',
                    'volumes' => [
                        0 => 'vapor_mysql:/var/lib/mysql',
                    ],
                    'restart' => 'always',
                    'environment' => [
                        'MYSQL_ROOT_PASSWORD' => 'secret',
                        'MYSQL_DATABASE' => 'vapor',
                        'MYSQL_USER' => 'vapor',
                        'MYSQL_PASSWORD' => 'secret',
                    ],
                ],
                'app' => [
                    'image' => static::$images[$this->option('php') ? $this->option('php') : '7.3'],
                    'depends_on' => [
                        0 => 'mysql',
                        1 => 'redis',
                    ],
                    'restart' => 'always',
                    'init' => true,
                ],
            ],
            'volumes' => [
                'vapor_mysql' => [],
                'vapor_redis' => [],
            ],
        ];
    }
}
