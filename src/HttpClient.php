<?php
namespace CORSProxy;

use \CORSProxy\HttpClientInterface;
use \CORSProxy\Response;

class HttpClient implements HttpClientInterface
{

    public function requestGet(string $url, array $headers): Response
    {
        $response = \Requests::get($url, $headers);
        return new Response($response->status_code, [
            'Content-Type' => $response->headers['content-type'],
            'Content-Length' => $response->headers['content-length'] ?? strlen($response->body)
        ], $response->body);
    }
}
