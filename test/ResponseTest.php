<?php
namespace CORSProxy\Test;

use \PHPUnit\Framework\TestCase;
use \CORSProxy\Response;


class ResponseTest extends TestCase
{

    public function testIsTextContent()
    {
        $response1 = new Response(200, ['Content-Type' => 'text/plain']);
        $this->assertTrue($response1->isTextContent());

        $response2 = new Response(200, ['Content-Type' => 'text/plain charset=UTF-8']);
        $this->assertTrue($response2->isTextContent());

        $response3 = new Response(200, ['Content-Type' => 'text/html']);
        $this->assertTrue($response3->isTextContent());

        $response4 = new Response(200, ['Content-Type' => 'text/html charset=UTF-8']);
        $this->assertTrue($response4->isTextContent());

        $response5 = new Response(200, ['Content-Type' => 'image/png']);
        $this->assertFalse($response5->isTextContent());
    }

    public function testWithHeaders()
    {
        $response = new Response(200, ['Content-Length' => '100'], 'test');
        $fixedResponse = $response->withHeaders(['Content-Type' => 'text/plain']);

        $this->assertEquals([
            'Content-Length' => '100'
        ], $response->getHeaders());
        $this->assertEquals([
            'Content-Length' => '100',
            'Content-Type' => 'text/plain'
        ], $fixedResponse->getHeaders());
    }

    public function testWithEncodedBody()
    {
        $utf8Body = 'テスト';
        $sjisBody = mb_convert_encoding($utf8Body, 'SJIS', 'UTF-8');
        $response = new Response(200, [
            'Content-Length' => '100',
            'Content-Type' => 'text/plain'
        ], $sjisBody);

        $this->assertEquals($sjisBody, $response->getBody());
        $this->assertEquals($utf8Body, $response->withEncodedBody('SJIS')->getBody());
        $this->assertEquals($sjisBody, $response->withEncodedBody('SJIS', 'SJIS')->getBody());
    }
}
