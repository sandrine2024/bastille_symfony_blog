<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\AdminPostFormType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class PostController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository,
        private CategoryRepository $categoryRepository
    )
    {
    }

    #[Route('/post/list', name: 'admin_post_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $this->postRepository->findAll()
        ]);
    }


    #[Route('/post/create', name: 'admin_post_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {

        // Vérifier s'il existe au moins une catégorie avant de continuer
        if ( \count($this->categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Pour rediger un article, vous devez créer au moins une catégorie");
            return $this->redirectToRoute('admin_category_index');
        }

        $post = new Post();
        
        $form = $this->createForm(AdminPostFormType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            /**
             * Récupérons l'utilisateur connecté
             * 
             * @var User
             */
            $user = $this->getUser();

            $post->setUser($user);
            $post->setCreatedAt(new DateTimeImmutable());
            $post->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($post);
            $this->em->flush();

            $this->addFlash("success", "L'article a été créé et sauvegardé");

            return $this->redirectToRoute("admin_post_index");
        }

        return $this->render('pages/admin/post/create.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/post/{id<\d+>}/publish', name: 'admin_post_publish', methods: ['POST'])]
    public function publish(Post $post, Request $request): Response
    {

        if ( $this->isCsrfTokenValid("publish_post_{$post->getId()}", $request->request->get('_csrf_token')) ) 
        {
            // Si l'article n'a pas encore été publié
            if ( false === $post->isPublished() ) 
            {
                // Publier l'article
                $post->setPublished(true);
    
                // Mettre à jour sa date de publication
                $post->setPublishedAt(new DateTimeImmutable());
                
                // Mettre à jour sa date de modification
                $post->setUpdatedAt(new DateTimeImmutable());
    
                // Demander au manager des entités de préparer la requête de modification
                $this->em->persist($post);
    
                // Générer le message flash expliquant que l'article a été publié
                $this->addFlash("success", "Cet article a été publié.");
            }
            else
            {
                // Retirer l'article de la liste des publications
                $post->setPublished(false);
    
                // Mettre une valeur nulle la date de publication
                $post->setPublishedAt(null);
    
                // Mettre à jour sa date de modification
                $post->setUpdatedAt(new DateTimeImmutable());
    
                // Demander au manager des entités de préparer la requête de modification
                $this->em->persist($post);
    
                // Générer le message expliquant de l'article a été retiré de la liste des publications
                $this->addFlash("success", "Cet article a été retiré de la liste des publications.");
            }

            // Demander au manager des entités d'exécuter la requête préparée
            $this->em->flush();
        }

        // Effectuer une redirection vers la page listant les articles 
            // Puis, arrêter l'exécution du script
        return $this->redirectToRoute('admin_post_index');
    }


    #[Route('/post/{id<\d+>}/show', name: 'admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render("pages/admin/post/show.html.twig", [
            'post' => $post
        ]);
    }


    #[Route('/post/{id<\d+>}/edit', name: 'admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request): Response
    {

        // Vérifier s'il existe au moins une catégorie avant de continuer
        if ( \count($this->categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Pour rediger un article, vous devez créer au moins une catégorie");
            return $this->redirectToRoute('admin_category_index');
        }

        $form = $this->createForm(AdminPostFormType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            /**
             * Récupérons l'utilisateur connecté
             * 
             * @var User
             */
            $user = $this->getUser();

            $post->setUser($user);
            $post->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($post);
            $this->em->flush();

            $this->addFlash("success", "L'article a été modifié et sauvegardé");

            return $this->redirectToRoute("admin_post_index");
        }

        return $this->render("pages/admin/post/edit.html.twig", [
            'post' => $post,
            "form" => $form->createView()
        ]);
    }


    #[Route('/post/{id<\d+>}/delete', name: 'admin_post_delete', methods: ['POST'])]
    public function delete(Post $post, Request $request): Response
    {
        if ( $this->isCsrfTokenValid('delete_post_'.$post->getId(), $request->request->get('_csrf_token')) ) 
        {
            $this->addFlash('success', "L'article {$post->getTitle()} a été supprimé");

            $this->em->remove($post);
            $this->em->flush();
        }

        return $this->redirectToRoute("admin_post_index");
    }

}
