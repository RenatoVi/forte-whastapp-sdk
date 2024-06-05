<?php

namespace ForteTecnologia\Whastapp\Rest;

use ForteTecnologia\Whastapp\Domain;
use ForteTecnologia\Whastapp\Http\Client;

class ApiBase extends Domain
{
    public function __construct(Client $client) {
        parent::__construct($client);

        $this->baseUrl = 'http://kaika.pedidoflix.test/';
    }
}