<?php

require __DIR__ . '/../vendor/autoload.php';

use ForteTecnologia\Whastapp\Http\Client;

$email = 'xxxxxxx@xxxxxxx.com';
$password = 'xxxxxxx';
$company  = 'xxxxxxx';

$client = new Client();
$token = $client->auth($email, $password);

$message = [
    'company' => $company,
    'to' => '63981413924',
    'message' => 'Hello, World!'
];

$response = $client->messages()->send($token, $message);

print_r($response->getContent());