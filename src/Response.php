<?php
namespace CORSProxy;

class Response
{

    private $statusCode;
    private $headers;
    private $body;

    public function __construct(int $statusCode, array $headers = [], string $body = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isTextContent(): bool
    {
        return preg_match("/^text\//", $this->headers['Content-Type']) === 1;
    }

    public function withEncodedBody(string $fromCharset, string $toCharset = 'UTF-8'): Response
    {
        if (!$this->isTextContent() || $fromCharset === $toCharset) {
            return new self(
                $this->statusCode,
                $this->headers,
                $this->body
            );
        }
        return new self(
            $this->statusCode,
            $this->headers,
            mb_convert_encoding($this->body, $toCharset, $fromCharset)
        );
    }

    public function withHeaders(array $headers): Response
    {
        return new self(
            $this->statusCode,
            array_merge($this->headers, $headers),
            $this->body
        );
    }
}
