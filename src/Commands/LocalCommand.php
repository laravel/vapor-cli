<?php

namespace Laravel\VaporCli\Commands;

use Exception;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class LocalCommand extends Command
{
    /**
     * The Docker for various PHP versions.
     *
     * @var array
     */
    public static $images = [
        '7.3' => 'laravelphp/vapor:php73',
        '7.4' => 'laravelphp/vapor:php74',
        '8.0' => 'laravelphp/vapor:php80',
        '8.1' => 'laravelphp/vapor:php81',
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
     * @return int
     *
     * @throws Exception
     */
    public function handle()
    {
        $options = array_slice($_SERVER['argv'], $this->option('php') ? 3 : 2);

        $status = 0;

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
            ], $options)),
            $status
        );

        unlink($dockerComposePath);

        return $status;
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
            'version'  => '3.7',
            'services' => [
                'redis' => [
                    'image'   => 'redis:alpine',
                    'volumes' => [
                        'vapor_redis:/data',
                    ],
                    'restart' => 'always',
                ],
                'mysql' => [
                    'image'   => 'mysql:8.0',
                    'volumes' => [
                        0 => 'vapor_mysql:/var/lib/mysql',
                    ],
                    'restart'     => 'always',
                    'environment' => [
                        'MYSQL_ROOT_PASSWORD' => 'secret',
                        'MYSQL_DATABASE'      => 'vapor',
                        'MYSQL_USER'          => 'vapor',
                        'MYSQL_PASSWORD'      => 'secret',
                    ],
                ],
                'app' => [
                    'image'      => static::$images[$this->option('php') ? $this->option('php') : '8.0'],
                    'depends_on' => [
                        0 => 'mysql',
                        1 => 'redis',
                    ],
                    'restart' => 'always',
                    'init'    => true,
                ],
            ],
            'volumes' => [
                'vapor_mysql' => [],
                'vapor_redis' => [],
            ],
        ];
    }
}
