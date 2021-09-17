<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Regions;

trait ProvidesSelectionMenus
{
    /**
     * Allow the user to select a cloud provider from a list.
     *
     * @param  string  $message
     * @return string
     */
    protected function determineProvider($message)
    {
        if ($this->input->hasOption('provider') && $this->option('provider')) {
            return $this->option('provider');
        }

        $providers = $this->vapor->providers();

        if (empty($providers)) {
            Helpers::abort('Please link a cloud provider to your account before proceeding.');
        }

        if (count($providers) === 1) {
            return $providers[0]['id'];
        }

        return $this->menu(
            $message,
            collect($providers)->mapWithKeys(function ($provider) {
                return [$provider['id'] => $provider['name']];
            })->all()
        );
    }

    /**
     * Allow the user to select a network from a list.
     *
     * @param  string  $message
     * @return string
     */
    protected function determineNetwork($message)
    {
        if ($this->input->hasOption('network') && $this->option('network')) {
            return $this->option('network');
        }

        $networks = collect($this->vapor->networks())->filter(function ($network) {
            return $network['status'] == 'available';
        })->all();

        if (empty($networks)) {
            Helpers::abort('Please create a network and allow it to finish provisioning before proceeding.');
        }

        if (count($networks) === 1) {
            return $networks[0]['id'];
        }

        return $this->menu(
            $message,
            collect($networks)->mapWithKeys(function ($network) {
                return [$network['id'] => $network['name']];
            })->all()
        );
    }

    /**
     * Allow the user to choose a certificate from the list.
     *
     * @param  string  $message
     * @param  array  $certificates
     * @return string
     */
    protected function chooseCertificate($message, array $certificates)
    {
        return $this->menu(
            $message,
            collect($certificates)->mapWithKeys(function ($certificate) {
                $time = Helpers::time_ago($certificate['created_at']);

                return [$certificate['id'] => $certificate['domain'].' ('.$time.')'];
            })->all()
        );
    }

    /**
     * Allow the user to choose a region for the operation.
     *
     * @param  string  $message
     * @return string
     */
    protected function determineRegion($message)
    {
        return $this->menu($message, Regions::available());
    }
}
