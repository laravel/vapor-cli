<?php

namespace Laravel\VaporCli;

use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Exceptions\RequestFailedException;

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
        $assetFiles = $this->getAssetFiles($assetPath);

        collect($assetFiles)
            ->chunk(400)
            ->map
            ->all()
            ->each(function ($chunkOfAssetFiles) use ($assetPath, $vapor, $artifact, $fresh) {
                $requests = $this->getAuthorizedAssetRequests(
                    $vapor,
                    $artifact,
                    $chunkOfAssetFiles,
                    $fresh
                );

                $this->executeStoreAssetRequests($requests['store'], $assetPath);
                $this->executeCopyAssetRequests($requests['copy'], $assetPath);

                $vapor->recordArtifactAssets(
                    $artifact['id'],
                    $chunkOfAssetFiles
                );
            });
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

        if (! empty($requests)) {
            try {
                $storage->executeStoreRequests($requests, $assetPath, function ($request) use ($assetPath) {
                    Helpers::step('<comment>Uploading Asset:</comment> '.$request['path'].' ('.Helpers::kilobytes($assetPath.'/'.$request['path']).')');
                });
            } catch (RequestFailedException $e) {
                $request = $requests[$e->getIndex()];

                Helpers::line("<fg=red>Uploading:</> {$request['path']}");
                Helpers::write($e->getMessage());

                exit(1);
            }
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

        if (! empty($requests)) {
            try {
                $storage->executeCopyRequests($requests, function ($request) use ($assetPath) {
                    Helpers::step('<fg=magenta>Copying Unchanged Asset:</> '.$request['path'].' ('.Helpers::kilobytes($assetPath.'/'.$request['path']).')');
                });
            } catch (RequestFailedException $e) {
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
