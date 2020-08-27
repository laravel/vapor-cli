<?php

namespace Laravel\VaporCli\Commands\Output;

use DateTime;
use DateTimeInterface;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Models\Deployment;

class DeploymentSuccess
{
    /**
     * Render the output.
     *
     * @param \Laravel\VaporCli\Models\Deployment $deployment
     * @param \DateTimeInterface                  $startedAt
     *
     * @return void
     */
    public function render(Deployment $deployment, DateTimeInterface $startedAt)
    {
        $time = (new DateTime())->diff($startedAt)->format('%im%Ss');

        Helpers::line();
        Helpers::line("<info>Project deployed successfully.</info> ({$time})");

        if ($deployment->hasTargetDomains()) {
            $this->displayTargetDomains($deployment);
        }

        if ($deployment->vanityDomain()) {
            Helpers::line();

            Helpers::table([
                '<comment>Deployment ID</comment>',
                '<comment>Environment URL (Copied To Clipboard)</comment>',
            ], [[
                "<options=bold>{$deployment->id}</>",
                "<options=bold>https://{$deployment->vanityDomain()}</>",
            ]]);
        }
    }

    /**
     * Display the target domains for the deployment domains.
     *
     * @param \Laravel\VaporCli\Models\Deployment $deployment
     *
     * @return void
     */
    protected function displayTargetDomains(Deployment $deployment)
    {
        Helpers::line();

        if ($deployment->created_custom_domain) {
            Helpers::line('<comment>Custom domain created:</comment> Custom domains may take up to an hour to become active after provisioning.');

            Helpers::line();
        }

        Helpers::table([
            'Domain', 'Alias / CNAME',
        ], collect($deployment->target_domains)->map(function ($target, $domain) {
            return [$domain, $target['domain']];
        })->all());
    }
}
