<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Model\Board;
use App\Model\PidApi;
use App\Model\PIDApi\request\PidApiDeparturesRequest;
use App\Model\PIDApi\request\PidApiStopsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function index(Request $request): Response
    {
        $board = new Board();

        $settings = [
            'Sídliště Čakovice' => null,
            'Krystalová' => function ($item) {
                $route = $item['route_number'] ?? '';
                $stop = $item['stop_id'] ?? '';

                return ($route !== '136' && $stop === 'U114Z3');
            },
            'Praha-Čakovice' => function ($item) {
                $destination = $item['destination'] ?? '';

                return (mb_strpos($destination, 'Praha') !== false);
            },
            'Králova' => function ($item) {
                $stop = $item['stop_id'] ?? '';

                return ($stop === 'U293Z2P');
            },
            'Cukrovar Čakovice' => function ($item) {
                $stop = $item['stop_id'] ?? '';

                return ($stop === 'U63Z2P');
            },
        ];

        $responseData = $board->getData($settings);

        return $this->render('board.html.twig', [
            'now' => time(),
            'departures' => $responseData
        ]);
    }

    public function rawStops(Request $request): Response
    {
        $responseData = '';
        $api = new PidApi(HttpClient::create([]));
        $names = $request->query->get('names');
        if (!empty($names)) {
            $request = new PidApiStopsRequest();
            $request->names = explode(', ', $names);
            $response = $api->get($request);

            $responseData = '<pre>' .
                            var_export($response->getByKey('features'), true) .
                            '</pre>';
        }

        return new Response(
            '
            <form><input name="names" value="' . $names . '"><input type="submit" value="Search"></form>
            ' .
            $responseData
        );
    }

    public function rawDepartures(Request $request): Response
    {
        $responseData = '';
        $api = new PidApi(HttpClient::create([]));
        $names = $request->query->get('names');
        if (!empty($names)) {
            $request = new PidApiDeparturesRequest();
            $request->names = explode(', ', $names);
            $response = $api->get($request);

            $responseData = '<pre>' .
                            var_export($response->getByKey('departures'), true) .
                            '</pre>';
        }

        return new Response(
            '
            <form><input name="names" value="' . $names . '"><input type="submit" value="Search"></form>
            ' .
            $responseData
        );
    }
}