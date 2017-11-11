<?php
namespace CORSProxy\Test;

use \PHPUnit\Framework\TestCase;
use \CORSProxy\ProxyRequest;


class ProxyRequestTest extends TestCase
{

    public function testGetUrl()
    {
        $request = new ProxyRequest([
            'q' => 'http://test.com/path/to/file'
        ], []);
        $this->assertEquals('http://test.com/path/to/file', $request->getUrl());
    }

    public function testGetOrigin()
    {
        $request = new ProxyRequest([], [
            'HTTP_ORIGIN' => 'http://example.com'
        ]);
        $this->assertEquals('http://example.com', $request->getOrigin());
    }

    public function testGetHttpMethod()
    {
        $request = new ProxyRequest([], [
            'REQUEST_METHOD' => 'GET'
        ]);
        $this->assertEquals('GET', $request->getHttpMethod());
    }

    public function testGetXFromCharset()
    {
        $request = new ProxyRequest([], [
            'HTTP_X_FROM_CHARSET' => 'SJIS'
        ]);
        $this->assertEquals('SJIS', $request->getXFromCharset());
        $this->assertEquals('UTF-8', (new ProxyRequest([], []))->getXFromCharset());
    }

    public function testGetRequestHeaders()
    {
        $request = new ProxyRequest([], [
            'HTTP_USER_AGENT' => 'Test Client'
        ]);
        $this->assertEquals([
            'User-Agent' => 'Test Client'
        ], $request->getRequestHeaders());
    }

    public function testGetAuthKey()
    {
        $request = new ProxyRequest([], [
            'HTTP_AUTHORIZATION' => 'Bearer abcdefg12345'
        ]);
        $this->assertEquals('abcdefg12345', $request->getAuthKey());
    }

}
