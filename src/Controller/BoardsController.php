<?php

namespace App\Controller;

use App\Model\Board;
use App\Service\BoardRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BoardsController extends AbstractController
{
    private BoardRegistry $registry;

    public function __construct(BoardRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function index(Request $request): Response
    {
        $name = $request->attributes->get('_route_params')['name'] ?? '';

        return $this->render('board.html.twig', [
            'boardName' => $name,
        ]);
    }
}
