<?php

namespace Phpbb\DevHooks\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method ResponseInterface get(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface head(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface put(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface patch(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface delete(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface getAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface headAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface putAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface postAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface patchAsync(string|UriInterface $uri, array $options = [])
 * @method PromiseInterface deleteAsync(string|UriInterface $uri, array $options = [])
 */
class OpenShiftClient
{
    private $token;

    private $client;
    private $tokenFile;
    private $certFile;

    public function __construct(Client $client, string $tokenFile, string $certFile = null)
    {
        $this->tokenFile = $tokenFile;
        $this->client = $client;
        $this->certFile = $certFile;
    }

    public function __call($method, $args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
        }

        $uri = $args[0];
        $options = isset($args[1]) ? $args[1] : [];
        $options = $this->completeOptions($options);

        return substr($method, -5) === 'Async'
            ? $this->client->requestAsync(substr($method, 0, -5), $uri, $options)
            : $this->client->request($method, $uri, $options);
    }

    private function completeOptions(array $options) : array
    {
        if (!isset($options[RequestOptions::HEADERS])) {
            $options[RequestOptions::HEADERS] = [];
        }

        if (!isset($options[RequestOptions::HEADERS]['Authorization'])) {
            $options[RequestOptions::HEADERS]['Authorization'] = 'Bearer '.$this->getToken();
        }

        if ($this->certFile !== null && !isset($options[RequestOptions::CERT])) {
            $options[RequestOptions::VERIFY] = $this->certFile;
        }

        return $options;
    }

    private function getToken() : string
    {
        if ($this->token === null) {
            if (!\file_exists($this->tokenFile) || !\is_readable($this->tokenFile)) {
                throw new \RuntimeException('The token file does nt exists or is unreadable');
            }

            $this->token = \file_get_contents($this->tokenFile);
        }

        return $this->token;
    }
}
