<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUseType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;








class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/users", name="admin_user")
     */
    public function usersList(UserRepository $users): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $users->findAll(),
        ]);
    }


    /**
     * @Route("/admin/users/edit/{id}", name="admin_userEdit")
     */
    public function usersEdit(User $user, Request $request): Response
    {
        $form = $this->createForm(EditUseType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $this->addFlash('message', 'utilisateur modifier avec succes');


            return $this->redirectToRoute('admin_user');
        }
        return $this->render('admin/userEdit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }
}
