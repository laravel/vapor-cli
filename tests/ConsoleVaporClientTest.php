<?php

namespace Laravel\VaporCli\Tests;

use GuzzleHttp\Psr7\Response;
use Laravel\VaporCli\ConsoleVaporClient;
use PHPUnit\Framework\TestCase;

class ConsoleVaporClientTest extends TestCase
{
    /**
     * @dataProvider validationResponseDataProvider
     */
    public function test_validation_errors_are_extracted_from_the_response($response, $expected)
    {
        $client = new ConsoleVaporClientWithPublicErrors();

        $this->assertSame($expected, $client->validationErrorsFrom($response)->all());
    }

    public static function validationResponseDataProvider()
    {
        return [
            'a validation bag is flattened' => [
                new Response(422, [], json_encode([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'manifest' => ['The build step is required.', 'The id is invalid.'],
                    ],
                ])),
                ['The build step is required.', 'The id is invalid.'],
            ],

            'a message is used when there is no validation bag' => [
                new Response(422, [], json_encode(['message' => 'The given data was invalid.'])),
                ['The given data was invalid.'],
            ],

            // A load balancer in front of the API can answer a 400 / 422 with
            // an HTML page rather than the JSON bag the API would have sent.
            'a non-JSON body is shown on a single line' => [
                new Response(400, [], "<html>\n<head><title>400 Bad Request</title></head>\n<body>\n<center><h1>400 Bad Request</h1></center>\n</body>\n</html>\n"),
                ['<html> <head><title>400 Bad Request</title></head> <body> <center><h1>400 Bad Request</h1></center> </body> </html>'],
            ],

            'an empty body falls back to the status code' => [
                new Response(400, [], ''),
                ['The API returned a 400 response with an empty body.'],
            ],
        ];
    }
}

class ConsoleVaporClientWithPublicErrors extends ConsoleVaporClient
{
    public function validationErrorsFrom($response)
    {
        return parent::validationErrorsFrom($response);
    }
}
