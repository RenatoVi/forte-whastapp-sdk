<?php

namespace ForteTecnologia\Whastapp\Rest;

use ForteTecnologia\Whastapp\Http\Response;

class MessageList extends ApiBase
{
    protected $uri;

    public function __construct($client)
    {
        parent::__construct($client);

        $this->uri = '/api/whatsapp/message';
    }

    public function send($token, $message): Response
    {
          return $this->request('POST', $this->uri, [], $message, ['Authorization' => 'Bearer ' . $token]);
    }
}
