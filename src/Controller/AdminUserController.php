<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("admin/")
 * @isGranted("ROLE_ADMIN")
 */
class AdminUserController extends AbstractController
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
     * User
     *
     * @Route("utilisateur", name="admin_user_index")
     * 
     * @return Response
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     * Show a specific user
     *
     * @Route("utilisateur/{id}", name="admin_user_show")
     * 
     * @param User $user
     * @return Response
     */
    public function show(User $user) 
    {
        return $this->render('admin/user/show.html.twig', [
            'body_class' => "user-show",
            'user' => $user
        ]);
    }

    /**
     * Edit user
     *
     * @Route("utilisateur/{id}/edition", name="admin_user_edit")
     * 
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, User $user)
    {
        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->flush();

            $this->addFlash(
                'success',
                "Les informations de l'utilisateur <strong>{$user->getFullName()}</strong> ont bien étaient modifié !"
            );

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'body_class' => "user-edit content-center",
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete user
     *
     * @Route("utilisateur/{id}/suppression", name="admin_user_delete")
     * 
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function delete(User $user)
    {
        $this->manager->remove($user);
        $this->manager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur <strong>{$user->getFullName()}</strong> a bien était supprimé !"
        );

        return $this->redirectToRoute('admin_user_index');
    }
}