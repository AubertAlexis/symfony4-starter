<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /**
     * Gestion de la connexion
     * 
     * @Route("/connexion", name="login")
     * 
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
           // get the login error if there is one
           $error = $authenticationUtils->getLastAuthenticationError();
           // last username entered by the user
           $lastUsername = $authenticationUtils->getLastUsername();
   
           return $this->render('account/login.html.twig', [
               'last_username' => $lastUsername,
               'error' => $error
           ]);
    }

    /**
     * Gestion de la deconnexion par Symfony
     * 
     * @Route("/deconnexion", name="logout")
     */
    public function logout(){}
}