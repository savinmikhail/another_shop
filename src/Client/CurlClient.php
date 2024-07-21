<?php

namespace App\Client;

use Http\Client\Curl\Client as BaseCurlClient;
use Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class CurlClient extends BaseCurlClient
{
    private array $defaultHeaders;
    private array $defaultQueryParams;
    private string $baseUri;

    public function __construct(string $baseUri, array $defaultHeaders = [], array $defaultQueryParams = [])
    {
        parent::__construct();
        $this->baseUri = $baseUri;
        $this->defaultHeaders = $defaultHeaders;
        $this->defaultQueryParams = $defaultQueryParams;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        foreach ($this->defaultHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $uri = $request->getUri();
        $queryParams = [];
        parse_str($uri->getQuery(), $queryParams);
        $queryParams = array_merge($queryParams, $this->defaultQueryParams);
        $uri = $uri->withQuery(http_build_query($queryParams));

        // Prepend the base URI
        $uri = $uri->withScheme('http')->withHost($this->baseUri);
        $request = $request->withUri($uri);

        return parent::sendRequest($request);
    }
}
