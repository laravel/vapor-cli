<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Path;
use Illuminate\Filesystem\Filesystem;

trait ParticipatesInBuildProcess
{
    protected $environment;
    protected $appPath;
    protected $path;
    protected $vaporPath;
    protected $buildPath;
    protected $files;

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
        $this->path = Path::current();
        $this->vaporPath = Path::vapor();
        $this->buildPath = Path::build();

        $this->files = new Filesystem;
    }
}
