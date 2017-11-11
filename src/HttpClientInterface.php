<?php
namespace CORSProxy;

use \CORSProxy\Response;

interface HttpClientInterface
{
    public function requestGet(string $url, array $headers): Response;
}
