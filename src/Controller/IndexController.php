<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Model\PidApi;
use App\Model\Board;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function index(Request $request): Response
    {
        $board = new Board();
        $responseData = $board->getData();
        
        return $this->render('board.html.twig', $responseData + [
            'now' => date('H:i')
        ]);
    }
    public function rawStops(Request $request): Response
    {
        $responseData = '';
        $api = new PidApi(HttpClient::create([]));
        $names = $request->query->get('names');
        if (!empty($names)) {
            $response = $api->getStops(explode(', ', $names));
            
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
            $response = $api->getDepartures(explode(', ', $names));
            
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