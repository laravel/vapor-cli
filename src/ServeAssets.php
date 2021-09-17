<?php

namespace Laravel\VaporCli;

use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Exceptions\CopyRequestFailedException;

class ServeAssets
{
    /**
     * Serve the project's assets.
     *
     * @param  \Laravel\VaporCli\ConsoleVaporClient  $vapor
     * @param  array  $artifact
     * @param  bool  $fresh
     * @return void
     */
    public function __invoke(ConsoleVaporClient $vapor, array $artifact, $fresh)
    {
        $assetPath = Path::build().'/assets';

        $requests = $this->getAuthorizedAssetRequests(
            $vapor,
            $artifact,
            $assetFiles = $this->getAssetFiles($assetPath),
            $fresh
        );

        $this->executeStoreAssetRequests($requests['store'], $assetPath);
        $this->executeCopyAssetRequests($requests['copy'], $assetPath);

        $vapor->recordArtifactAssets(
            $artifact['id'],
            $assetFiles
        );
    }

    /**
     * Execute the given requests to store assets.
     *
     * @param  array  $requests
     * @param  string  $assetPath
     * @return void
     */
    protected function executeStoreAssetRequests($requests, $assetPath)
    {
        $storage = Helpers::app(AwsStorageProvider::class);

        foreach ($requests as $request) {
            Helpers::step('<comment>Uploading Asset:</comment> '.$request['path'].' ('.Helpers::kilobytes($assetPath.'/'.$request['path']).')');

            $storage->store(
                $request['url'],
                array_merge($request['headers'], ['Cache-Control' => 'public, max-age=2628000']),
                $assetPath.'/'.$request['path']
            );
        }
    }

    /**
     * Execute the given requests to copy assets.
     *
     * @param  array  $requests
     * @param  string  $assetPath
     * @return void
     */
    protected function executeCopyAssetRequests($requests, $assetPath)
    {
        $storage = Helpers::app(AwsStorageProvider::class);

        foreach ($requests as $request) {
            Helpers::step('<fg=magenta>Copying Unchanged Asset:</> '.$request['path'].' ('.Helpers::kilobytes($assetPath.'/'.$request['path']).')');
        }

        if (! empty($requests)) {
            try {
                $storage->executeCopyRequests($requests);
            } catch (CopyRequestFailedException $e) {
                $request = $requests[$e->getIndex()];

                Helpers::line("<fg=red>Copying:</> {$request['path']}");
                Helpers::write($e->getMessage());

                exit(1);
            }
        }
    }

    /**
     * Get the pre-signed URLs for storing the artifact's assets.
     *
     * @param  \Laravel\VaporCli\ConsoleVaporClient  $vapor
     * @param  array  $artifact
     * @param  string  $assetFiles
     * @return array
     */
    protected function getAuthorizedAssetRequests(
        ConsoleVaporClient $vapor,
        array $artifact,
        array $assetFiles,
        bool $fresh
    ) {
        return $vapor->authorizeArtifactAssets(
            $artifact['id'],
            $assetFiles,
            $fresh
        );
    }

    /**
     * Get the asset files within the given directory.
     *
     * @param  string  $assetPath
     * @return array
     */
    protected function getAssetFiles($assetPath)
    {
        return collect(AssetFiles::relativePaths($assetPath))
                ->map(function ($path) use ($assetPath) {
                    return [
                        'path' => $path,
                        'hash' => md5_file($assetPath.'/'.$path),
                    ];
                })->all();
    }
}
