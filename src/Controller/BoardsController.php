<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Filters\FilterByDestinationPrefix;
use App\Filters\FilterByExcludeRouteNumber;
use App\Filters\FilterByRouteNumber;
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
        $id = mb_strtolower($id);
        switch ($id) {
            case 'from_work_hv':
                $trains = new FilterByRouteNumber(['S3', 'S30', 'S34', 'R21', 'R43', 'T3']);

                return [
                    [
                        'name' => 'Pod karlovem',
                        'query' => ['ids' => ['U560Z1P']],
                    ],
                    [
                        'name' => 'Praha Masarykovo nádraží',
                        'query' => ['ids' => ['U480Z301']],
                        'filters' => $trains
                    ],
                    [
                        'name' => 'Praha Hl.n.',
                        'query' => ['ids' => ['U142Z301']],
                        'filters' => $trains
                    ]
                ];
            case 'to_work_hv':
                $f_6_11 = new FilterByRouteNumber(['6', '11']);

                return [
                    [
                        'name' => 'IP Pavlova',
                        'query' => ['ids' => ['U190Z2P', 'U190Z4P']],
                        'filters' => $f_6_11
                    ],
                    [
                        'name' => 'Flora',
                        'query' => ['ids' => ['U118Z1P']],
                        'filters' => $f_6_11
                    ],
                    [
                        'name' => 'Masarykovo nádraží',
                        'query' => ['ids' => ['U480Z3P']],
                        'filters' => $f_6_11
                    ],
                    [
                        'name' => 'Jindřišská',
                        'query' => ['ids' => ['U203Z1P']],
                        'filters' => $f_6_11
                    ],
                ];
            case 'to_cakovice':
                $ids = ['U1000Z12P', 'U1000Z1', 'U1000Z12'];
                $f_136_58 = new FilterByRouteNumber(['136', '58']);

                return [
                    [
                        'name' => 'Letňany',
                        'query' => ['ids' => $ids],
                        'filters' => new FilterByRouteNumber(['136', '351'])
                    ],
                    [
                        'name' => 'Letňany - Za Avií',
                        'query' => ['ids' => $ids],
                        'filters' => new FilterByRouteNumber(['58', '377'])
                    ],
                    [
                        'name' => 'Vysočanská',
                        'query' => ['ids' => ['U474Z5P']],
                        'filters' => $f_136_58
                    ],
                    [
                        'name' => 'Palmovka',
                        'query' => ['ids' => ['U529Z4P']],
                        'filters' => $f_136_58
                    ],
                    [
                        'name' => 'Prosek',
                        'query' => ['ids' => ['U603Z1P']],
                        'filters' => $f_136_58
                    ],
                ];
            case 'to_work_maddz':
                return [
                    [
                        'name' => 'Poliklinika Budějovická (P)',
                        'query' => ['ids' => ['U50Z5P']],
                        'filters' => new FilterByRouteNumber(['117', '121'])
                    ],
                    [
                        'name' => 'Poliklinika Budějovická (L)',
                        'query' => ['ids' => ['U50Z6P']],
                        'filters' => new FilterByRouteNumber(['117', '121'])
                    ],
                    [
                        'name' => 'Kačerov A',
                        'query' => ['ids' => ['U228Z3P']],
                        'filters' => new FilterByRouteNumber(['106', '196', '150'])
                    ],
                    [
                        'name' => 'Praha Hl.n.',
                        'query' => ['ids' => ['U142Z301']],
                        'filters' => new FilterByRouteNumber(['S8', 'S88'])
                    ]
                ];
            case 'from_work_maddz':
                return [
                    [
                        'name' => 'Novodvorská (D)',
                        'query' => ['ids' => ['U497Z4P']],
                        'filters' => new FilterByRouteNumber(['117', '121'])
                    ],
                    [
                        'name' => 'Novodvorská (D) - Kačerov',
                        'query' => ['ids' => ['U497Z4P']],
                        'filters' => new FilterByRouteNumber(['106', '196', '150'])
                    ],
                    [
                        'name' => 'Lhotka',
                        'query' => ['ids' => ['U329Z2P']],
                        'filters' => new FilterByRouteNumber(['157'])
                    ],
                    [
                        'name' => 'Praha-Krč',
                        'query' => ['ids' => ['U1048Z301']],
                        'filters' => [
                            new FilterByRouteNumber(['S8', 'S88']),
                            new FilterByDestinationPrefix(['Praha'])
                        ]
                    ]
                ];
            default:
                return [
                    [
                        'name' => 'Sídliště Čakovice',
                        'query' => ['names' => ['Sídliště Čakovice']]
                    ],
                    [
                        'name' => 'Krystalová',
                        'query' => ['ids' => ['U114Z3']],
                        'filters' => new FilterByExcludeRouteNumber(['136'])
                    ],
                    [
                        'name' => 'Praha-Čakovice',
                        'query' => ['ids' => ['U3212Z301']],
                        'filters' => new FilterByDestinationPrefix(['Praha'])
                    ],
                    [
                        'name' => 'Za Avií',
                        'query' => ['ids' => ['U451Z2P', 'U451Z2']],
                        'past_count' => 3,
                        'future_count' => 15,
                        'filters' => new FilterByExcludeRouteNumber(['202'])
                    ]
                ];
        }
    }
}
