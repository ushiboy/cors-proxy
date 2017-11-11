<?php
namespace CORSProxy\Test;

use \PHPUnit\Framework\TestCase;
use \CORSProxy\Server;
use \CORSProxy\HttpClientInterface;
use \CORSProxy\Response;
use \CORSProxy\ProxyRequest;

class HttpDummyClient implements HttpClientInterface
{

    public $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    public function requestGet(string $url, array $headers): Response
    {
        return $this->response;
    }
}

class ServerTest extends TestCase
{

    public function testExecute()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient(new Response(200, [
            'Content-Length' => '100',
            'Content-Type' => 'text/plain',
        ], 'test')));
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('test', $response->getBody());
        $this->assertEquals([
            'Content-Length' => '100',
            'Content-Type' => 'text/plain',
            'Access-Control-Allow-Origin' => 'http://localhost',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }

    public function testExecute_WhenAllowMultiOrigin()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost,http://other.com', $digest, $this->createClient(new Response(200, [
            'Content-Length' => '100',
            'Content-Type' => 'text/plain',
        ], 'test')));
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://other.com',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'Content-Length' => '100',
            'Content-Type' => 'text/plain',
            'Access-Control-Allow-Origin' => 'http://other.com',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }


    public function testExecute_WhenPreFlightRequest()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient());
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'OPTIONS',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'Access-Control-Allow-Origin' => 'http://localhost',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }

    public function testExecute_WhenNotSupportHttpMethod()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient());
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals([
            'Access-Control-Allow-Origin' => 'http://localhost',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }

    public function testExecute_WhenFailAuthenticate()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient());
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals([
            'Access-Control-Allow-Origin' => 'http://localhost',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }

    public function testExecute_WhenInvalidOrigin()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient());
        $response = $server->execute(new ProxyRequest([
            'q' => 'http://example.com/',
        ], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://other.com',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(403, $response->getStatusCode());
        // 許可されていないoriginからのリクエストにAccess-Control-*は返さない
        $this->assertEquals([], $response->getHeaders());
    }

    public function testExecute_WhenInvalidUrl()
    {
        list($key, $digest) = $this->generateAuthKeyPairs();
        $server = new Server('http://localhost', $digest, $this->createClient());
        $response = $server->execute(new ProxyRequest([
            'q' => '/favicon.ico'
        ], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_AUTHORIZATION' => "Bearer $key",
            'HTTP_ORIGIN' => 'http://localhost',
            'HTTP_USER_AGENT' => 'Test Client'
        ]));
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'Access-Control-Allow-Origin' => 'http://localhost',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ], $response->getHeaders());
    }

    private function generateAuthKeyPairs()
    {
        $key = base64_encode(openssl_random_pseudo_bytes(54));
        $digest = password_hash($key, PASSWORD_BCRYPT, ['cost' => 10]);
        return [$key, $digest];
    }

    private function createClient(Response $response = null) {
        return new HttpDummyClient($response ?: new Response(404));
    }

}
