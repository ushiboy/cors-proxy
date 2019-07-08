<?php
namespace CORSProxy;

use \CORSProxy\HttpClientInterface;
use \CORSProxy\HttpClient;
use \CORSProxy\ProxyRequest;
use \CORSProxy\Response;
use Monolog\Logger;

class Server
{

    private $allowOriginList;

    private $authKeyDigest;

    private $httpClient;

    private $logger;

    public function __construct(
        string $allowOrigin,
        string $authKeyDigest,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->allowOriginList = $this->splitAllowOriginToList($allowOrigin);
        $this->authKeyDigest = $authKeyDigest;
        $this->httpClient = $httpClient ?: new HttpClient();
        $this->logger = new Logger('cors-proxy');
    }

    public function execute(ProxyRequest $request): Response
    {
        $method = $request->getHttpMethod();
        $origin = $request->getOrigin();
        $matchedOrigin = $this->matchAllowOrigin($origin);
        $accessControlHeaders = $this->getAccessControlHeaders($matchedOrigin);
        if ($method === 'OPTIONS') {
            // for preflight
            return new Response(200, $accessControlHeaders);
        }

        $authKey = $request->getAuthKey();
        if (!$this->authenticate($authKey) || $matchedOrigin === null) {
            $this->logger->error("auth key ($authKey) or origin ($origin) not match");
            return new Response(403, $accessControlHeaders);
        }

        if ($method !== 'GET') {
            $this->logger->error("Not support method ($method)");
            return new Response(405, $accessControlHeaders);
        }

        $url = $request->getUrl();
        if (!$this->isValidUrl($url)) {
            $this->logger->error("invalid url parameter ($url)");
            return new Response(400, $accessControlHeaders);
        }
        $response = $this->httpClient->requestGet($url, $request->getRequestHeaders())
            ->withHeaders($accessControlHeaders)
            ->withEncodedBody($request->getXFromCharset());
        return $response;
    }

    private function splitAllowOriginToList(string $allowOrigin): array
    {
        return array_map(function ($s) {
            return trim($s);
        }, preg_split('/,/', $allowOrigin, -1, PREG_SPLIT_NO_EMPTY));
    }

    private function authenticate(?string $authKey): bool
    {
        if (is_null($authKey)) {
            return false;
        }
        return password_verify($authKey, $this->authKeyDigest);
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function matchAllowOrigin(?string $origin): ?string
    {
        $i = array_search($origin, $this->allowOriginList);
        return $i !== false ? $this->allowOriginList[$i] : null;
    }

    private function getAccessControlHeaders(?string $matchedOrigin): array
    {
        return $matchedOrigin !== null ? [
            'Access-Control-Allow-Origin' => $matchedOrigin,
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization,X-From-Charset',
            'Access-Control-Max-Age' => 86400
        ] : [];
    }
}
