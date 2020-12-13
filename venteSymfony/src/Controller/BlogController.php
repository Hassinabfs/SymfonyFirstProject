<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;





class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(): Response
    {
        $repo = $this->getDoctrine()->getRepository(Produit::class);
        /** je veux discuter a doctrine qui va me donner un repository */
        $produits = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'produits' => $produits,
        ]);
    }


    /**
     * @Route("/",name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig');
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/blog/new",name="blog_ajout")
     * @Route("/blog/{id}/edit",name="blog_edit")
     */
    public function AMProduit(Produit $produit = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$produit) {
            $produit = new Produit();
        }

        $form = $this->createFormBuilder($produit)
            /**cette methode elle va creer un form qui va etre lier a mon article
         * pour le configurer faut lui donner des champs qu on a besoin avec add
         */
            ->add('nom')
            ->add('prix')
            ->add('image')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'titre'
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'titre'
            ])
            ->add('description')
            ->add('description_detaille')
            ->getForm();

        $form->handleRequest($request);
        /**demander au formulaire d'analyser la requete http qu'on a envoyer en parametre  */

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($produit);
            /**demander au manager de se preparer a faire persister l'article */
            $manager->flush();
            /**demander au manager de balancer la requete  */
            return $this->redirectToRoute('blog_show', ['id' => $produit->getId()]);
            /**demande de redirection a la page blog_show en precisant que l'id est celui de l' article ajouter */
        }


        return $this->render('blog/ajoutProduit.html.twig', [
            'formProduit' => $form->createView(),
            /**on va passer a twig un tableau qui contien les information qu on veut lui passer
             * et egalement une variable formProduit  qui va avoir le resultat de createView de formulaire*/
            'editMode' => $produit->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}",name="blog_show")
     */
    public function show($id): Response
    {
        $repo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $repo->find($id);
        return $this->render('blog/show.html.twig', [
            'produit' => $produit
        ]);
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/blog/delete/{id}",name="blog_delete")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($produit);
        $entityManager->flush();

        $response = new Response();
        $response->send();

        return $this->redirectToRoute('blog');
    }
}
