<?php
namespace CORSProxy;

class ProxyRequest
{

    private $queryStrings;

    private $server;

    private $headers;

    public function __construct(array $queryStrings, array $server)
    {
        $this->queryStrings = $queryStrings;
        $this->server = $server;
        $this->headers = $this->serverToHeaders($server);
    }

    public function getUrl(): string
    {
        return $this->queryStrings['q'] ?? '';
    }

    public function getOrigin(): string
    {
        return $this->headers['Origin'] ?? '';
    }

    public function getHttpMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getXFromCharset(): string
    {
        return $this->headers['X-From-Charset'] ?? 'UTF-8';
    }

    public function getRequestHeaders(): array
    {
        $src = $this->headers;
        $dist = [
            'User-Agent' => $src['User-Agent']
        ];
        if (isset($src['Accept'])) {
            $dist['Accept'] = $src['Accept'];
        }
        if (isset($src['Accept-Encoding'])) {
            $dist['Accept-Encoding'] = $src['Accept-Encoding'];
        }
        if (isset($src['Accept-Language'])) {
            $dist['Accept-Language'] = $src['Accept-Language'];
        }
        return $dist;
    }

    public function getAuthKey(): ?string
    {
        $authorization = $this->headers['Authorization'] ?? '';
        preg_match("/Bearer (.*)/", $authorization, $array_result);
        return $array_result[1] ?? null;
    }

    public function serverToHeaders(array $server)
    {
        $headers = [];
        foreach (array_keys($server) as $key) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $server[$key];
            }
        }
        return $headers;
    }
}
