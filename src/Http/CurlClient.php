<?php

namespace ForteTecnologia\Whastapp\Http;

use ForteTecnologia\Whastapp\Exceptions\EnvironmentException;

class CurlClient
{
    public const DEFAULT_TIMEOUT = 60;

    protected $curlOptions = [];

    public $lastRequest;
    public $lastResponse;

    public function __construct()
    {
        $this->client = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->client);
    }

    public function post($url, $data)
    {
        curl_setopt($this->client, CURLOPT_URL, $url);
        curl_setopt($this->client, CURLOPT_POST, 1);
        curl_setopt($this->client, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($this->client, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($this->client);

        return json_decode($response, true);
    }

    /**
     * @throws ErrorException
     * @throws EnvironmentException
     */
    public function request(string $method, string $url,
                            array  $params = [], array $data = [], array $headers = [],
                            string $user = null, string $password = null,
                            int    $timeout = null): Response {

        $options = $this->options($method, $url, $params, $data, $headers,
            $user, $password, $timeout);

        $this->lastRequest = $options;
        $this->lastResponse = null;

        try {
            if (!$curl = \curl_init()) {
                throw new EnvironmentException('Unable to initialize cURL');
            }

            if (!\curl_setopt_array($curl, $options)) {
                throw new EnvironmentException(\curl_error($curl));
            }

            if (!$response = \curl_exec($curl)) {
                throw new EnvironmentException(\curl_error($curl));
            }

            $parts = \explode("\r\n\r\n", $response, 3);

            list($head, $body) = (
                \preg_match('/\AHTTP\/1.\d 100 Continue\Z/', $parts[0])
                || \preg_match('/\AHTTP\/1.\d 200 Connection established\Z/', $parts[0])
                || \preg_match('/\AHTTP\/1.\d 200 Tunnel established\Z/', $parts[0])
            )
                ? array($parts[1], $parts[2])
                : array($parts[0], $parts[1]);

            $statusCode = \curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $responseHeaders = [];
            $headerLines = \explode("\r\n", $head);
            \array_shift($headerLines);
            foreach ($headerLines as $line) {
                list($key, $value) = \explode(':', $line, 2);
                $responseHeaders[$key] = $value;
            }

            \curl_close($curl);

            if (isset($options[CURLOPT_INFILE]) && \is_resource($options[CURLOPT_INFILE])) {
                \fclose($options[CURLOPT_INFILE]);
            }

            $this->lastResponse = new Response($statusCode, $body, $responseHeaders);

            return $this->lastResponse;
        } catch (\ErrorException $e) {
            if (isset($curl) && \is_resource($curl)) {
                \curl_close($curl);
            }

            if (isset($options[CURLOPT_INFILE]) && \is_resource($options[CURLOPT_INFILE])) {
                \fclose($options[CURLOPT_INFILE]);
            }

            throw $e;
        }
    }

    public function options(string $method, string $url,
                            array $params = [], array $data = [], array $headers = [],
                            string $user = null, string $password = null,
                            int $timeout = null): array {
        $timeout = $timeout ?? self::DEFAULT_TIMEOUT;
        $options = $this->curlOptions + [
                CURLOPT_URL => $url,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_INFILESIZE => Null,
                CURLOPT_HTTPHEADER => [],
                CURLOPT_TIMEOUT => $timeout,
            ];

        foreach ($headers as $key => $value) {
            $options[CURLOPT_HTTPHEADER][] = "$key: $value";
        }

        if ($user && $password) {
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Basic ' . \base64_encode("$user:$password");
        }

        $query = $this->buildQuery($params);
        if ($query) {
            $options[CURLOPT_URL] .= '?' . $query;
        }

        switch (\strtolower(\trim($method))) {
            case 'get':
                $options[CURLOPT_HTTPGET] = true;
                break;
            case 'post':
                $options[CURLOPT_POST] = true;
                if ($this->hasFile($data)) {
                    [$headers, $body] = $this->buildMultipartOptions($data);
                    $options[CURLOPT_POSTFIELDS] = $body;
                    $options[CURLOPT_HTTPHEADER] = \array_merge($options[CURLOPT_HTTPHEADER], $headers);
                }
                elseif (array_key_exists('Content-Type', $headers)) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                else {
                    $options[CURLOPT_POSTFIELDS] = $this->buildQuery($data);
                    $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
                }

                break;
            case 'put':
                // TODO: PUT doesn't used anywhere and it has strange implementation. Must investigate later
                $options[CURLOPT_PUT] = true;
                if ($data) {
                    if ($buffer = \fopen('php://memory', 'w+')) {
                        $dataString = $this->buildQuery($data);
                        \fwrite($buffer, $dataString);
                        \fseek($buffer, 0);
                        $options[CURLOPT_INFILE] = $buffer;
                        $options[CURLOPT_INFILESIZE] = \strlen($dataString);
                    } else {
                        throw new EnvironmentException('Unable to open a temporary file');
                    }
                }
                break;
            case 'head':
                $options[CURLOPT_NOBODY] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = \strtoupper($method);
        }

        return $options;
    }

    public function buildQuery(?array $params): string {
        $parts = [];
        $params = $params ?: [];

        foreach ($params as $key => $value) {
            if (\is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = \urlencode((string)$key) . '=' . \urlencode((string)$item);
                }
            } else {
                $parts[] = \urlencode((string)$key) . '=' . \urlencode((string)$value);
            }
        }

        return \implode('&', $parts);
    }

    private function hasFile(array $data): bool {
        foreach ($data as $value) {
            if ($value instanceof File) {
                return true;
            }
        }

        return false;
    }
}
