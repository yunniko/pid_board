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
        $masarykovo = [
            'name' => 'Masarykovo nádraží',
            'query' => ['ids' => ['U480Z301']],
            'filterCallback' => function ($item) {
                $route = $item->route_number ?? '';

                return (in_array($route, ['S3', 'S34', 'R43']));
            }
        ];
        $hlavni = [
            'name' => 'Hlavní nádraží',
            'query' => ['ids' => ['U142Z301']],
            'filterCallback' => function ($item) {
                $route = $item->route_number ?? '';

                return (in_array($route, ['R21']));
            }
        ];
        switch ($id) {
            case 'from_work_hv':
                return [
                    [
                        'name' => 'Pod karlovem',
                        'query' => ['ids' => ['U560Z1P']],
                    ],
                    $masarykovo,
                    $hlavni
                ];
            case 'to_work_hv':
                return [
                    [
                        'name' => 'IP Pavlova',
                        'query' => ['ids' => ['U190Z2P', 'U190Z4P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['6', '11']));
                        }
                    ],
                    [
                        'name' => 'Flora',
                        'query' => ['ids' => ['U118Z1P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['11']));
                        }
                    ],
                    [
                        'name' => 'Masarykovo nádraží',
                        'query' => ['ids' => ['U480Z3P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['6']));
                        }
                    ],
                    [
                        'name' => 'Jindřišská',
                        'query' => ['ids' => ['U203Z1P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['6']));
                        }
                    ],
                ];
            case 'to_cakovice':
                $ids = ['U1000Z12P', 'U1000Z1', 'U1000Z12'];

                return [
                    [
                        'name' => 'Letňany',
                        'query' => ['ids' => $ids],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['136', '351']));
                        }
                    ],
                    [
                        'name' => 'Letňany - Cukrovar',
                        'query' => ['ids' => $ids],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['140', '377', '158']));
                        }
                    ],
                    [
                        'name' => 'Vysočanská',
                        'query' => ['ids' => ['U474Z5P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['136']));
                        }
                    ],
                    [
                        'name' => 'Palmovka',
                        'query' => ['ids' => ['U529Z11P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['140']));
                        }
                    ],
                    [
                        'name' => 'Prosek',
                        'query' => ['ids' => ['U603Z1P']],
                        'filterCallback' => function ($item) {
                            $route = $item->route_number ?? '';

                            return (in_array($route, ['136', '140']));
                        }
                    ],
                ];
            /*case 'vysocanska':
                return [

                ];*/
            default:
                return [
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
                    ],
                    [
                        'name' => 'Za Avií',
                        'query' => ['ids' => ['U451Z2P', 'U451Z2']],
                        'past_count' => 3,
                        'future_count' => 15
                    ]
                ];
        }
    }
}
