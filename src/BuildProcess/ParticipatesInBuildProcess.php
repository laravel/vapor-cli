<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;

trait ParticipatesInBuildProcess
{
    protected $environment;
    protected $appPath;
    protected $vendorPath;
    protected $path;
    protected $vaporPath;
    protected $buildPath;
    protected $files;
    protected $vapor;

    /**
     * Create a new project builder.
     *
     * @param  string|null  $environment
     * @return void
     */
    public function __construct($environment = null)
    {
        $this->environment = $environment;
        $this->appPath = Path::app();
        $this->vendorPath = Path::vendor();
        $this->path = Path::current();
        $this->vaporPath = Path::vapor();
        $this->buildPath = Path::build();
        $this->files = new Filesystem();
        $this->vapor = Helpers::app(ConsoleVaporClient::class);
    }
}
