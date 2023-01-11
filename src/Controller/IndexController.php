<?php
// src/Controller/LuckyController.php
namespace App\Controller;

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
        return $this->render('index.html.twig');
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

    public function renderDebug($data)
    {
        return new Response(
            '<pre>' .
            var_export($data, true) .
            '</pre>'
        );
    }
}