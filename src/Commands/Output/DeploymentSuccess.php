<?php

namespace Laravel\VaporCli\Commands\Output;

use DateTime;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Models\Deployment;

class DeploymentSuccess
{
    /**
     * Render the output.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @param  \DateTimeInterface  $startedAt
     * @return void
     */
    public function render(Deployment $deployment, DateTimeInterface $startedAt)
    {
        $time = (new DateTime())->diff($startedAt)->format('%im%Ss');

        Helpers::line();
        Helpers::line("<info>Project deployed successfully.</info> ({$time})");

        if ($deployment->hasTargetDomains()) {
            $this->displayDnsRecordsChanges($deployment);
        }

        if ($deployment->hasVanityDomain()) {
            $this->displayVanityUrl($deployment);
        }

        $this->displayFunctionUrl($deployment);

        $this->displayAssetDomainDnsRecordChanges($deployment);
    }

    /**
     * Display the DNS Records changes related to this environment.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @return void
     */
    protected function displayDnsRecordsChanges(Deployment $deployment)
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

        $vapor = Helpers::app(ConsoleVaporClient::class);

        collect($vapor->zones($deployment->project['team_id']))->filter(function ($zone) use ($deployment) {
            return in_array($zone['zone'], $deployment->root_domains);
        })->filter(function ($zone) use ($vapor) {
            return collect($vapor->records($zone['id']))->contains(function ($record) {
                return Carbon::parse($record['updated_at'])->greaterThanOrEqualTo(Carbon::now()->subWeek());
            });
        })->each(function ($zone) {
            $zone = $zone['zone'];

            Helpers::line("The DNS records of the zone <comment>$zone</comment> have changed in the last week. If you self-manage the DNS settings of this zone, please run <comment>vapor record:list $zone</comment> and update the DNS settings of the domain accordingly.");
        });
    }

    /**
     * Display the vanity domain associated with this environment.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @return void
     */
    protected function displayVanityUrl(Deployment $deployment)
    {
        Helpers::line();

        Helpers::table([
            '<comment>Deployment ID</comment>',
            '<comment>Environment URL</comment>',
        ], [[
            "<options=bold>{$deployment->id}</>",
            "<options=bold>https://{$deployment->vanityDomain()}</>",
        ]]);
    }

    /**
     * Display the function URL associated with this environment.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @return void
     */
    protected function displayFunctionUrl(Deployment $deployment)
    {
        if ($deployment->hasTargetDomains() || $deployment->hasVanityDomain()) {
            return;
        }

        if (! $deployment->hasFunctionUrl()) {
            return;
        }

        Helpers::line();

        Helpers::table([
            '<comment>Deployment ID</comment>',
            '<comment>Environment URL</comment>',
        ], [[
            "<options=bold>{$deployment->id}</>",
            "<options=bold>{$deployment->functionUrl()}</>",
        ]]);
    }

    /**
     * Display the DNS Records changes related to the asset domain.
     *
     * @param  \Laravel\VaporCli\Models\Deployment  $deployment
     * @return void
     */
    protected function displayAssetDomainDnsRecordChanges(Deployment $deployment)
    {
        if (! $assetDomain = $deployment->manifest['asset-domain'] ?? null) {
            return;
        }

        Helpers::line();

        if ($deployment->created_asset_domain) {
            Helpers::line('<comment>Custom asset domain created:</comment> Custom domains may take up to an hour to become active after provisioning.');

            Helpers::line();
        }

        Helpers::table([
            'Domain', 'Alias / CNAME',
        ], [[
            $assetDomain, $deployment->project['cloudfront_domain'],
        ]]);
    }
}
