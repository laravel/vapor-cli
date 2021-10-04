<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Str;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;

class ValidateOctaneDependencies
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (Manifest::octane($this->environment)) {
            Helpers::step('<options=bold>Validating Octane Dependencies</>');

            $this->warnAboutMissingDependencies();
        }
    }

    /**
     * Check and warn about missing Octane dependencies.
     *
     * @return $this
     */
    protected function warnAboutMissingDependencies()
    {
        $this->ensurePackageRequirement('laravel/vapor-core', '2.14.0');
        $this->ensurePackageRequirement('laravel/octane', '1.0.12');
        $this->ensurePackageRequirement('laravel/framework', '8.62.0');

        return $this;
    }

    /**
     * Ensures the given package requirement.
     *
     * @param  string  $package
     * @param  string  $version
     * @return void
     */
    protected function ensurePackageRequirement($package, $version)
    {
        if (! file_exists($file = $this->appPath.'/vendor/composer/installed.json')) {
            return;
        }

        $currentVersion = optional(collect(json_decode(file_get_contents($file)))
                ->pipe(function ($composer) {
                    return collect($composer->get('packages', $composer));
                })
                ->where('name', $package)
                ->first())->version;

        if (Str::startsWith($currentVersion ?? '', 'dev-') || Str::endsWith($currentVersion ?? '', 'x-dev')) {
            return;
        }

        if (is_null($currentVersion) || version_compare(ltrim($currentVersion, 'v'), $version) < 0) {
            Helpers::abort(
                'Using Octane requires "'.$package.'": "^'.$version.'".'
            );
        }
    }
}
