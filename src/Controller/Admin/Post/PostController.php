<?php
namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\AdminPostFormType;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
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
        private PostRepository $postRepository
    )
    {
    }

    #[Route('/post/list', name: 'admin_post_index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $this->postRepository->findAll()
        ]);
    }

    #[Route('/post/create', name: 'admin_post_create', methods: ['GET','POST'])]
    public function create(Request $request): Response
    {
        $post = new Post();
        // Crée une nouvelle instance de l'entité Post.



        $form = $this->createForm(AdminPostFormType::class,$post);
        // Crée un formulaire en utilisant la classe AdminPostFormType et associe-le à l'objet $post.

        $form->handleRequest($request);
        // Traite la requête HTTP actuelle et la lie au formulaire. Cela permet de remplir le formulaire avec les données soumises (si elles existent).

        if ( $form->isSubmitted() && $form->isValid())
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
            // $post->setPublishedAt(new DateTimeImmutable());

            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash("success", "L'article a été ajouté et sauvegardé.");

            return $this->redirectToRoute('admin_post_index');
        }

      

        return $this->render('pages/admin/post/create.html.twig', [
            "form" =>$form->createView(),
        ]);
    }
}
