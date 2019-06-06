<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Form\UpdatePasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("admin/parametre/")
 * @isGranted("ROLE_ADMIN")
 */
class AdminParameterController extends AbstractController
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
     * Update password
     *
     * @Route("", name="admin_parameter_index")
     * 
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function edit(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(UpdatePasswordType::class, $this->getUser());
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $oldPassword = $request->request->get('update_password')['oldPassword'];
            $newPassword = $request->request->get('update_password')['plainPassword']['password'];

            if ($passwordEncoder->isPasswordValid($this->getUser(), $oldPassword)) {
                $newEncodedPassword = $passwordEncoder->encodePassword($user, $newPassword);
                $user->setPassword($newEncodedPassword);
                
                $this->manager->flush();
    
                $this->addFlash(
                    'success',
                    'Votre mot de passe à bien été changé !'
                );

                $this->manager->refresh($user);    
                return $this->redirectToRoute('admin_account_index');
            } else {

                $this->addFlash(
                    'danger',
                    "L'ancien mot de passe n'est pas le bon !"
                );
                
                $this->manager->refresh($user);
                return $this->redirectToRoute('admin_parameter_index');
            }
        }

        return $this->render('admin/parameter/update_password.html.twig', [
            'body_class' => "update-password content-center",
            'form' => $form->createView()
        ]);
    }
}