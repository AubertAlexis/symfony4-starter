<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserEditType;
use App\Form\UserAddType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("admin/utilisateur/")
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
     * @Route("", name="admin_user_index")
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
     * Add user
     *
     * @Route("ajouter", name="admin_user_add")
     * 
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function add(UserPasswordEncoderInterface $encoder, Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserAddType::class, $user);

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
                'success',
                "L'utilisateur <strong>{$user->getFullName()}</strong> a bien était créé !"
            );

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/add.html.twig', [
            'body_class' => "user-add content-center",
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Show a specific user
     *
     * @Route("{id}", name="admin_user_show")
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
     * @Route("{id}/edition", name="admin_user_edit")
     * 
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function edit(User $user, Request $request)
    {
        // Check user role and redirect if is admin
        if($user->getRoleTitle() == "Administrateur"){
            $this->addFlash(
                'danger',
                "Vous n'avez pas le droit de modifier les informations de <strong>{$user->getFullName()}</strong>."
            );

            return $this->redirectToRoute('admin_user_index');
        }

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
     * @Route("{id}/suppression", name="admin_user_delete")
     * 
     * @param User $user
     * @return Response
     */
    public function delete(User $user)
    {
        // Check user role and redirect if is admin
        if($user->getRoleTitle() == "Administrateur"){
            $this->addFlash(
                'danger',
                "Vous ne pouvez pas supprimer l'utilisateur <strong>{$user->getFullName()}</strong>."
            );

            return $this->redirectToRoute('admin_user_index');
        }

        $this->manager->remove($user);
        $this->manager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur <strong>{$user->getFullName()}</strong> a bien était supprimé !"
        );

        return $this->redirectToRoute('admin_user_index');
    }
}