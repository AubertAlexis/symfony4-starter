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
use App\Repository\UserRepository;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
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
     * @Route("/admin")
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
   
           return $this->render('security/login.html.twig', [
                'error' => $error !== null,
                'username' => $username,
                'body_class' => 'bg-gradient-primary'
           ]);
    }

    /**
     * Check for role and redirect
     *
     * @Route("/connexion/succes", name="login_success")
     * 
     * @return Response
     */
    public function onLoginSuccess()
    {
        if ($this->isGranted('ROLE_ADMIN')) return $this->redirectToRoute('admin_dashboard_index');
        else return $this->redirectToRoute('home_index');
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
            dump($user);
            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash(
                "success",
                "Votre compte à bien était créé !"
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
            'body_class' => "bg-gradient-primary"
        ]);
    }

    /**
     * @Route("/mot-de-passe", name="forgot_password")
     *
     * @return void
     */
    public function forgotPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer){

        if($request->isMethod("POST")){
            $email = $request->request->get('_email');

            $user = $userRepository->findOneByEmail($email);

            if ($user === null) {
                $this->addFlash('danger', 'Aucun utilisateur ne possède cet email !');

                return $this->redirectToRoute('forgot_password');
            }

            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);

                $this->manager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());

                return $this->redirectToRoute('forgot_password');
            }

            $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('aaubert.test@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/reset_password.html.twig',[
                            'url' => $url,
                            'date' => new \DateTime()
                        ]
                    ),
                    'text/html'
                );
 
            $mailer->send($message);

            $this->addFlash('success', "Email bien envoyé à l'adresse <strong>{$user->getEmail()}</strong>");

        }

        return $this->render('security/forgot_password.html.twig', [
            'body_class' => "bg-gradient-primary"
        ]);
    }

    /**
     * @Route("/nouveau-mot-de-passe/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {

        if ($request->isMethod('POST')) {

            $user = $userRepository->findOneByResetToken($token);

            if ($user === null) {
                $this->addFlash('danger', 'Jeton inconnu');

                return $this->redirectToRoute('forgot_password');
            }

            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword(
                $user, 
                $request->request->get('_password')
            ));

            $this->manager->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');

            return $this->redirectToRoute('login');
        }else {

            return $this->render('security/reset_password.html.twig', [
                'token' => $token,
                'body_class' => "bg-gradient-primary"
            ]);
        }

    }
}