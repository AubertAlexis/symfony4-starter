<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Home
     *
     * @Route("", name="home_index")
     * 
     * @return Response
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }
}