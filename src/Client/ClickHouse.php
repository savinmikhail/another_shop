<?php

declare(strict_types=1);

namespace App\Client;

use DateTimeZone;
use SimPod\ClickHouseClient\Client\PsrClickHouseClient;
use SimPod\ClickHouseClient\Client\Http\RequestFactory;
use SimPod\ClickHouseClient\Format\JsonEachRow;
use Nyholm\Psr7\Factory\Psr17Factory;

class ClickHouse
{
    private PsrClickHouseClient $clickHouseClient;

    public function __construct(CurlClient $client)
    {
        $psr17Factory = new Psr17Factory();

        $this->clickHouseClient = new PsrClickHouseClient(
            $client,
            new RequestFactory(
                $psr17Factory,
                $psr17Factory
            ),
            [],
            new DateTimeZone('UTC')
        );
    }

    public function getUsersOnlineByHour(): array
    {
        $query = 'SELECT toHour(StartTime) AS hour, count(DISTINCT UserID) AS users_online
                  FROM datasets.visits_v1
                  GROUP BY hour
                  ORDER BY hour';

        $output = $this->clickHouseClient->select($query, new JsonEachRow());

        return $output->data;
    }
}
