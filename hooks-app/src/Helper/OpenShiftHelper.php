<?php

namespace Phpbb\DevHooks\Helper;

use GuzzleHttp\RequestOptions;

class OpenShiftHelper
{
    private $client;

    public function __construct(OpenShiftClient $client)
    {
        $this->client = $client;
    }

    public function createEnvironment(string $envName, array $labels = []) : array
    {
        $response = $this->client->post('/oapi/v1/projectrequests', [
            RequestOptions::JSON => [
                'kind' => 'Project',
                'apiVersion' => 'v1',
                'metadata' => [
                    'name' => $envName,
                    'labels' => $labels,
                ]
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Cannot create environment');
        }

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    public function deleteEnvironment(string $envName) : void
    {
        $response = $this->client->delete('/oapi/v1/projects/'.$envName);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Cannot create environment');
        }
    }
}
