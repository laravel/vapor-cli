<?php

namespace Laravel\VaporCli;

use Laravel\VaporCli\Aws\AwsStorageProvider;

class ServeAssets
{
    /**
     * Serve the project's assets.
     *
     * @param \Laravel\VaporCli\ConsoleVaporClient $vapor
     * @param array                                $artifact
     *
     * @return void
     */
    public function __invoke(ConsoleVaporClient $vapor, array $artifact)
    {
        $assetPath = Path::build().'/assets';

        $requests = $this->getAuthorizedAssetRequests(
            $vapor,
            $artifact,
            $assetFiles = $this->getAssetFiles($assetPath)
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
     * @param array  $requests
     * @param string $assetPath
     *
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
     * @param array  $requests
     * @param string $assetPath
     *
     * @return void
     */
    protected function executeCopyAssetRequests($requests, $assetPath)
    {
        $storage = Helpers::app(AwsStorageProvider::class);

        foreach ($requests as $request) {
            Helpers::step('<fg=magenta>Copying Unchanged Asset:</> '.$request['path'].' ('.Helpers::kilobytes($assetPath.'/'.$request['path']).')');
        }

        $storage->executeCopyRequests($requests);
    }

    /**
     * Get the pre-signed URLs for storing the artifact's assets.
     *
     * @param \Laravel\VaporCli\ConsoleVaporClient $vapor
     * @param array                                $artifact
     * @param string                               $assetFiles
     *
     * @return array
     */
    protected function getAuthorizedAssetRequests(
        ConsoleVaporClient $vapor,
        array $artifact,
        array $assetFiles
    ) {
        return $vapor->authorizeArtifactAssets(
            $artifact['id'],
            $assetFiles
        );
    }

    /**
     * Get the asset files within the given directory.
     *
     * @param string $assetPath
     *
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
