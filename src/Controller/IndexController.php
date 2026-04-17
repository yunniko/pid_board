<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    public function stops(): Response
    {
        return $this->render('stops.html.twig');
    }

    public function departures(): Response
    {
        return $this->render('departures.html.twig');
    }
}
