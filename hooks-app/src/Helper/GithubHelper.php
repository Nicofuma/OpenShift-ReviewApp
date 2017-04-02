<?php

namespace Phpbb\DevHooks\Helper;

use Github\Client;

class GithubHelper
{
    protected $client;
    protected $apiToken;
    protected $authed = false;

    public function __construct(Client $client, $apiToken)
    {
        $this->client = $client;
        $this->apiToken = (string) $apiToken;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAuthenticatedClient()
    {
        if (!$this->authed) {
            $this->client->authenticate(
                $this->apiToken,
                Client::AUTH_HTTP_TOKEN
            );
        }
        return $this->client;
    }

    public function setCommitStatus(array $commitData, array $statusData, string $state, string $description, string $targetUrl = null)
    {
        $options = [
            'state' => $state,
            'description' => $description,
            'context' => $statusData['context'],
        ];

        if (!empty($targetUrl)) {
            $options['target_url'] = $targetUrl;
        }

        $response = $this
            ->getAuthenticatedClient()
            ->repository()
            ->statuses()
            ->create(
                $commitData['owner'],
                $commitData['repository'],
                $commitData['sha'],
                $options
            )
        ;
    }
}
