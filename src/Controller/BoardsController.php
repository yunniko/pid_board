<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Model\Board;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BoardsController extends AbstractController
{
    public function index(Request $request): Response
    {
        $board = new Board();
        $name = $request->attributes->get('_route_params')['name'] ?? '';
        $settings = $this->getSettings($name);

        $responseData = $board->getData($settings);

        return $this->render('board.html.twig', [
            'now' => time(),
            'departures' => $responseData
        ]);
    }

    public function getSettings($id)
    {
        switch ($id) {
            case 'ippavlova':
                return [

                ];
            case 'letnany':
                return [
                    'Letňany#1|U1000Z12P,U1000Z1,U1000Z12' => function ($item) {
                        $route = $item['route_number'] ?? '';

                        return (in_array($route, ['136']));
                    },
                    'Letňany#2|U1000Z12P,U1000Z1,U1000Z12' => function ($item) {
                        $route = $item['route_number'] ?? '';

                        return (in_array($route, ['351', '140']));
                    }
                ];
            case 'vysocanska':
                return [

                ];
            default:
                return [
                    'Sídliště Čakovice' => null,
                    'Krystalová|U114Z3' => function ($item) {
                        $route = $item['route_number'] ?? '';

                        return ($route !== '136');
                    },
                    'Praha-Čakovice' => function ($item) {
                        $destination = $item['destination'] ?? '';

                        return (mb_strpos($destination, 'Praha') !== false);
                    },
                    'Králova|U293Z2P' => null,
                    'Cukrovar Čakovice|U63Z2P' => null
                ];
        }
    }
}