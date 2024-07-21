<?php

namespace App\Factory;

use App\Client\CurlClient;

class CurlClientFactory
{
    public static function createClient(string $baseUri, string $username, string $password, string $database): CurlClient
    {
        $defaultHeaders = [
            'X-ClickHouse-User' => $username,
            'X-ClickHouse-Key' => $password,
        ];

        $defaultQueryParams = [
            'database' => $database,
        ];

        return new CurlClient($baseUri, $defaultHeaders, $defaultQueryParams);
    }
}
