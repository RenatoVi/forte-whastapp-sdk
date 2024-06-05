<?php

namespace ForteTecnologia\Whastapp;

use ForteTecnologia\Whastapp\Http\Client;
use ForteTecnologia\Whastapp\Http\Response;

abstract class Domain
{
    protected $client;

    protected $baseUrl;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->baseUrl = '';
    }

    public function absoluteUrl(string $uri): string {
        return \implode('/', [\trim($this->baseUrl, '/'), \trim($uri, '/')]);
    }

    public function request(string $method, string $uri,
                            array $params = [], array $data = [], array $headers = [],
                            string $user = null, string $password = null,
                            int $timeout = null): Response
    {
        $url = $this->absoluteUrl($uri);

        return $this->client->request(
            $method,
            $url,
            $params,
            $data,
            $headers,
            $user,
            $password,
            $timeout
        );
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}