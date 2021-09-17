<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

trait RetrievesProviderCredentials
{
    /**
     * Get the credentials for an AWS provider.
     *
     * @return array
     */
    protected function getAWSCredentials()
    {
        if (file_exists(Helpers::home().'/.aws/credentials') &&
            Helpers::confirm('Would you like to choose credentials from your AWS credentials file', true)) {
            return $this->getCredentialsFromFile();
        }

        return [
            'key'    => Helpers::ask('What is your AWS user key'),
            'secret' => Helpers::secret('What is your AWS user secret'),
        ];
    }

    /**
     * Get the AWS credentials from the credentials file.
     *
     * @return array
     */
    protected function getCredentialsFromFile()
    {
        $credential = $this->determineCredential(
            $credentials = parse_ini_file(Helpers::home().'/.aws/credentials', true)
        );

        return [
            'key'    => $credentials[$credential]['aws_access_key_id'],
            'secret' => $credentials[$credential]['aws_secret_access_key'],
        ];
    }

    /**
     * Determine which credential to load from the credential file.
     *
     * @param  array  $credentials
     * @return string
     */
    protected function determineCredential(array $credentials)
    {
        return $this->menu(
            'Which set of credentials would you like to use?',
            collect($credentials)->mapWithKeys(function ($credential, $key) {
                return [$key => $key];
            })->all()
        );
    }
}
