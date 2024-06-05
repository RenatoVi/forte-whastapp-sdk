# ForteTecnologia WhatsApp SDK

Este repositório contém um exemplo de como utilizar o SDK da ForteTecnologia para enviar mensagens via WhatsApp.

## Instalação

Para instalar o SDK, utilize o Composer. No terminal, execute o seguinte comando:

```sh
composer require forte-tecnologia/whastapp-sdk
```

## Utilização

Na pasta `examples` você encontrará um arquivo `send-message.php` que exemplifica como enviar uma mensagem via WhatsApp.

```php

<?php
require __DIR__ . '/../vendor/autoload.php';

use ForteTecnologia\Whastapp\Http\Client;

// Credenciais
$email = 'xxxxxxx@xxxxxxx.com';
$password = 'xxxxxxx';

// exemplo:
// https://xxxxxxx.fortetecnologiaapp.com.br
$company  = 'xxxxxxx';

// Inicializa o cliente e autentica
$client = new Client();
$token = $client->auth($email, $password);

// Cria a mensagem
$message = [
    'company' => $company,
    'to' => '63999999999',
    'message' => 'Bom dia!'
];

// Envia a mensagem
$response = $client->messages()->send($token, $message);
```

## Credenciais

Para utilizar o SDK, você precisa de um e-mail e senha cadastrados na ForteTecnologia. Caso você não tenha, entre em contato conosco.

