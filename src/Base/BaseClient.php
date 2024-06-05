<?php

namespace ForteTecnologia\Whastapp\Base;

use ForteTecnologia\Whastapp\Exceptions\EnvironmentException;
use ForteTecnologia\Whastapp\Http\CurlClient;

class BaseClient
{
    /**
     * @throws EnvironmentException
     */
    public function request(
        string $method,
        string $uri,
        array $params = [],
        array $data = [],
        array $headers = [],
        string $username = null,
        string $password = null,
        int $timeout = null
    ) {

        $headers['Accept'] = 'application/json';

        return $this->getClient()->request('POST', $uri, $params, $data, $headers);
    }

    public function getClient(): CurlClient
    {
        return new CurlClient();
    }
}