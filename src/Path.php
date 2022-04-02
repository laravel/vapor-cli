<?php

namespace Laravel\VaporCli;

class Path
{
    /**
     * Get the path to the built application.
     *
     * @return string
     */
    public static function app()
    {
        return static::build().'/app';
    }

    /**
     * Get the path to the vendor directory.
     *
     * @return string
     */
    public static function vendor()
    {
        return static::build().'/vendor';
    }

    /**
     * Get the path to the deployment artifact.
     *
     * @return string
     */
    public static function artifact()
    {
        return getcwd().'/.vapor/build/app.zip';
    }

    /**
     * Get the path to the deployment artifact.
     *
     * @return string
     */
    public static function vendorArtifact()
    {
        return getcwd().'/.vapor/build/vendor.zip';
    }

    /**
     * Get the path to the built application's public directory.
     *
     * @return string
     */
    public static function assets()
    {
        return getcwd().'/.vapor/build/app/public';
    }

    /**
     * Get the path to the Vapor build directory.
     *
     * @return string
     */
    public static function build()
    {
        return static::vapor().'/build';
    }

    /**
     * Get the path to the current working directory.
     *
     * @return string
     */
    public static function current()
    {
        return getcwd();
    }

    /**
     * Get the path to the project's manifest file.
     *
     * @return string
     */
    public static function manifest()
    {
        return Helpers::app('manifest');
    }

    /**
     * Get the path to the default manifest file location.
     *
     * @return string
     */
    public static function defaultManifest()
    {
        return getcwd().'/vapor.yml';
    }

    /**
     * Get the path to the hidden Vapor directory.
     *
     * @return string
     */
    public static function vapor()
    {
        return getcwd().'/.vapor';
    }

    /**
     * Get the path to the environment's dockerfile.
     *
     * @param  string  $environment
     * @return string
     */
    public static function dockerfile($environment)
    {
        return getcwd().'/'.Manifest::dockerfile($environment);
    }
}
