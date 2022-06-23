<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;

class RemovePintBinary
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->files->exists($path = Path::app().'/vendor/laravel/pint/builds/pint')) {
            Helpers::step('<options=bold>Removing Laravel Pint Binary</>');

            $this->files->delete($path);
        }
    }
}
