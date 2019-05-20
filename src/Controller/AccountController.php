<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Form\RegistrationType;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    private $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Login
     * 
     * @Route("/connexion", name="login")
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
           // Get last error
           $error = $utils->getLastAuthenticationError();

           // Get last username
           $username = $utils->getLastUsername();
   
           return $this->render('account/login.html.twig', [
                'error' => $error !== null,
                'username' => $username,
                'body_class' => 'bg-gradient-primary'
           ]);
    }

    /**
     * Logout
     * 
     * @Route("/deconnexion", name="logout")
     */
    public function logout(){}

    /**
     * Registration for a new user
     *
     * @Route("/inscription", name="registration")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function registration(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $encoder->encodePassword(
                    $user, 
                    $user->getPassword()
                )
            );

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash(
                "success",
                "Votre compte à bien était créé !"
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView(),
            'body_class' => "bg-gradient-primary"
        ]);
    }
}