<?php

namespace ForteTecnologia\Whastapp\Rest;

use ForteTecnologia\Whastapp\Http\Response;

class Auth extends ApiBase
{
    protected $uri;

    public function __construct($client)
    {
        parent::__construct($client);

        $this->uri = '/api/auth/authenticate';
    }

    public function authenticate($credentials): Response
    {
          $credentials['device_name'] = 'sdk';
          return $this->request('POST', $this->uri, [], $credentials);
    }
}
