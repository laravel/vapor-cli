<?php

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Support\Carbon;
use Laravel\VaporCli\Helpers;

class ValidateApiToken
{
    use ParticipatesInBuildProcess;

    /**
     * Execute the build process step.
     *
     * @return void
     */
    public function __invoke()
    {
        if (isset($_ENV['VAPOR_API_TOKEN']) || getenv('VAPOR_API_TOKEN')) {
            Helpers::step('<options=bold>Validating API Token</>');

            $this->warnAboutExpiringDate();
        }
    }

    /**
     * Check and warn about the API Token expiring date, if needed.
     *
     * @return $this
     */
    protected function warnAboutExpiringDate()
    {
        $token = $this->vapor->currentToken();

        $expiresAt = Carbon::parse($token['expires_at']);

        if ($expiresAt->diffInMonths(Carbon::now()) < 6) {
            Helpers::warn(
                '- Your API token expires on '.$expiresAt->isoFormat('MMM Do, YYYY')
                .'. To ensure deployments don\'t fail after the expiration date, you may'
                .' generate a new API token in your Vapor API settings dashboard.'
            );
        }

        return $this;
    }
}
