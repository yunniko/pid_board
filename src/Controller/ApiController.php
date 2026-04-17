<?php

namespace App\Controller;

use App\Model\Board;
use App\Model\PIDApi\PidApi;
use App\Model\PIDApi\request\PidApiDeparturesRequest;
use App\Model\PIDApi\request\PidApiStopsRequest;
use App\Service\BoardRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{
    private BoardRegistry $registry;

    public function __construct(BoardRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function boards(): JsonResponse
    {
        return $this->json($this->registry->getBoardList());
    }

    public function board(Request $request): JsonResponse
    {
        $name = $request->attributes->get('_route_params')['name'] ?? '';
        $settings = $this->registry->getBoardSettings($name);
        $board = new Board();
        $data = $board->getData($settings);

        return $this->json(array_map(static function (array $panel): array {
            return [
                'stop' => $panel['stop'],
                'departures' => array_values(array_map(
                    static fn($item) => $item->toArray(),
                    $panel['departures']
                )),
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
            return [];
        }
        $api = new PidApi(HttpClient::create([]));
        $apiRequest->names = explode(', ', $names);
        $response = $api->get($apiRequest);

        return array_values(array_map(
            static fn($item) => $item->toArray(),
            $response->getData()
        ));
    }
}
