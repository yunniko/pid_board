<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Model\Board;
use App\Model\PIDApi\PidApi;
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
            [
                'name' => 'Sídliště Čakovice',
                'query' => ['names' => ['Sídliště Čakovice']]
            ],
            [
                'name' => 'Krystalová',
                'query' => ['ids' => ['U114Z3']],
                'filterCallback' => function ($item) {
                    $route = $item->route_number ?? '';

                    return ($route !== '136');
                }
            ],
            [
                'name' => 'Praha-Čakovice',
                'query' => ['names' => ['Praha-Čakovice']],
                'filterCallback' => function ($item) {
                    $destination = $item->destination ?? '';

                    return (mb_strpos($destination, 'Praha') !== false);
                }
            ]
        ];

        $responseData = [
            'now' => time(),
            'departures' => $board->getData($settings)
        ];

        return $this->render('board.html.twig', $responseData);
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
                            var_export($response->getData(), true) .
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

    public function renderDebug($data)
    {
        return new Response(
            '<pre>' .
            var_export($data, true) .
            '</pre>'
        );
    }
}