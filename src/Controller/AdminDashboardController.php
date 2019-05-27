<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\UserRepository;

/**
 * @Route("admin/")
 * @isGranted("ROLE_ADMIN")
 */
class AdminDashboardController extends AbstractController
{
    /**
     * Dashboard
     *
     * @Route("tableau-de-bord", name="admin_dashboard_index")
     * 
     * @return Response
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render('admin/dashboard/index.html.twig',[
            'users' => $userRepository->countUser()
        ]);
    }
}