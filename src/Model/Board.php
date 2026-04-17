<?php

namespace App\Model;

use App\Model\PIDApi\PidApi;
use App\Model\PIDApi\request\PidApiDeparturesRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class Board
{
    public $defaultSettings = [
        'minutesBefore' => 30,
        'minutesAfter' => 180,
        'total' => 50,
        'limit' => 50
    ];

    private PidApi $api;
    private LoggerInterface $logger;

    public function __construct(PidApi $api, ?LoggerInterface $logger = null)
    {
        $this->api = $api;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getData($settings)
    {
        $defaults = $this->defaultSettings;
        $result = [];
        foreach ($settings as $data) {
            $query = $data['query'] ?? [];
            $filters = $data['filters'] ?? null;
            $name = $data['name'] ?? '';
            $panel = [
                'stop' => $name,
                'departures' => [],
                'error' => null,
            ];
            try {
                $settingsObject = new PidApiDeparturesRequest(array_merge($defaults, $query));
                $response = $this->api->get($settingsObject);
                $departures = $response->getFilteredData($filters);
                $panel['departures'] = $this->filterByTime(
                    $departures,
                    'departure_predicted_ts',
                    $data['past_count'] ?? 1,
                    $data['future_count'] ?? 5,
                    $data['max_timerange_minutes'] ?? 90
                );
            } catch (Throwable $e) {
                $this->logger->error('Board stop fetch failed', [
                    'stop' => $name,
                    'error' => $e->getMessage(),
                ]);
                $panel['error'] = $e->getMessage();
            }
            $result[] = $panel;
        }

        return $result;
    }

    private function filterByTime(
        $array,
        $columnTimestamp,
        $pastCount = 1,
        $futureCount = 5,
        $maxTimerangeMinutes = 90
    ) {
        $past = [];
        $future = [];
        $now = time();
        $maxTimerangeSeconds = $maxTimerangeMinutes * 60;
        foreach ($array as $item) {
            $itemTime = $item->$columnTimestamp ?? 0;
            if ($itemTime < $now && $now - $itemTime < $maxTimerangeSeconds) {
                $past[] = $item;
            } else if ($itemTime - $now < $maxTimerangeSeconds) {
                $future[] = $item;
            }
        }

        return array_merge(array_slice($past, $pastCount * -1), array_slice($future, 0, $futureCount));
    }
}
