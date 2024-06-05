<?php

namespace ForteTecnologia\Whastapp\Http;

use ForteTecnologia\Whastapp\Base\BaseClient;
use ForteTecnologia\Whastapp\Rest\Auth;
use ForteTecnologia\Whastapp\Rest\MessageList;

/**
 * Class Client
 * @package ForteTecnologia\Whastapp\Http
 * @property MessageList
 */
class Client extends BaseClient
{
    protected $username;
    protected $password;
    protected $token;

    public function auth($username, $password): string
    {
        $auth = new Auth($this);

        $response = $auth->authenticate([
            'email' => $username,
            'password' => $password,
            'device_name' => 'sdk'
        ]);

        if (isset($response->getContent()['type']) && $response->getContent()['type'] === 'error') {
            throw new \RuntimeException($response->getContent()['message']);
        }

        return $response->getContent()['token'];
    }

    public function messages(): MessageList
    {
        return new MessageList($this);
    }
}