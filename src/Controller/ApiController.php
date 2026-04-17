<?php

namespace App\Controller;

use App\Model\Board;
use App\Model\PIDApi\PidApi;
use App\Model\PIDApi\request\PidApiDeparturesRequest;
use App\Model\PIDApi\request\PidApiStopsRequest;
use App\Service\BoardRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ApiController extends AbstractController
{
    private BoardRegistry $registry;
    private Board $board;
    private PidApi $api;
    private LoggerInterface $logger;

    public function __construct(
        BoardRegistry $registry,
        Board $board,
        PidApi $api,
        ?LoggerInterface $logger = null
    ) {
        $this->registry = $registry;
        $this->board = $board;
        $this->api = $api;
        $this->logger = $logger ?? new NullLogger();
    }

    public function boards(): JsonResponse
    {
        return $this->json($this->registry->getBoardList());
    }

    public function board(Request $request): JsonResponse
    {
        $name = $request->attributes->get('_route_params')['name'] ?? '';
        $settings = $this->registry->getBoardSettings($name);
        $data = $this->board->getData($settings);

        return $this->json(array_map(static function (array $panel): array {
            return [
                'stop' => $panel['stop'],
                'departures' => array_values(array_map(
                    static fn($item) => $item->toArray(),
                    $panel['departures']
                )),
                'error' => $panel['error'] ?? null,
            ];
        }, $data));
    }

    public function stops(Request $request): JsonResponse
    {
        return $this->json($this->rawSearch($request, new PidApiStopsRequest()));
    }

    public function departures(Request $request): JsonResponse
    {
        return $this->json($this->rawSearch($request, new PidApiDeparturesRequest()));
    }

    private function rawSearch(Request $request, $apiRequest): array
    {
        $names = $request->query->get('names');
        if (empty($names)) {
            return ['data' => [], 'error' => null];
        }
        $apiRequest->names = explode(', ', $names);
        try {
            $response = $this->api->get($apiRequest);
            $data = array_values(array_map(
                static fn($item) => $item->toArray(),
                $response->getData()
            ));
            return ['data' => $data, 'error' => null];
        } catch (Throwable $e) {
            $this->logger->error('Raw search failed', [
                'names' => $names,
                'error' => $e->getMessage(),
            ]);
            return ['data' => [], 'error' => $e->getMessage()];
        }
    }
}
