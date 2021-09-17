<?php

namespace Laravel\VaporCli\BuildProcess;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Finder\Finder;

class HarmonizeConfigurationFiles
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        Helpers::step('<options=bold>Harmonizing Configuration Files</>');

        $configFiles = (new Finder())->files()->in($this->appPath.'/config');

        foreach ($configFiles as $file) {
            file_put_contents(
                $file->getRealPath(),
                $this->replaceAwsEnvironmentVariables($file)
            );
        }
    }

    /**
     * Replace the AWS environment variables with dummy variables so they will not be used.
     *
     * The keys and secrets are automatically injected by Lambda.
     *
     * @param  \SplFileInfo  $file
     * @return string
     */
    protected function replaceAwsEnvironmentVariables($file)
    {
        return str_replace([
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_SESSION_TOKEN',
        ], [
            'NULL_AWS_ACCESS_KEY_ID',
            'NULL_AWS_SECRET_ACCESS_KEY',
            'NULL_AWS_SESSION_TOKEN',
        ], file_get_contents($file->getRealPath()));
    }
}
