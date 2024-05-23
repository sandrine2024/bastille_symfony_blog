<?php

namespace App\Controller\Admin\Tag;

use App\Entity\Tag;
use DateTimeImmutable;
use App\Form\AdminTagFormType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class TagController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private TagRepository $tagRepository
    )
    {
        
    }

    #[Route('/tag/list', name: 'admin_tag_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/tag/index.html.twig', [
            "tags" => $this->tagRepository->findAll()
        ]);
    }


    #[Route('/tag/create', name: 'admin_tag_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $tag = new Tag();

        $form = $this->createForm(AdminTagFormType::class, $tag);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $tag->setCreatedAt(new DateTimeImmutable());
            $tag->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash('success', "Le tag a été ajouté avec succès.");

            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('pages/admin/tag/create.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/tag/{id<\d+>}/edit', name: 'admin_tag_edit', methods: ['GET','POST'])]
    public function edit(Tag $tag, Request $request): Response
    {
        $form = $this->createForm(AdminTagFormType::class, $tag);

        $tagName = $tag->getName();

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $tag->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash('success', "Le tag {$tagName} a été modifié en {$form->getData()->getName()}");
            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render("pages/admin/tag/edit.html.twig", [
            "form" => $form->createView(),
            "tag" => $tag
        ]);
    }


    #[Route('/tag/{id<\d+>}/delete', name: 'admin_tag_delete', methods: ['POST'])]
    public function delete(Tag $tag, Request $request): Response
    {
        if ( $this->isCsrfTokenValid('delete_tag_'.$tag->getId(), $request->request->get('_csrf_token')) ) 
        {
            $this->addFlash('success', "Le tag {$tag->getName()} a été supprimé");

            $this->em->remove($tag);
            $this->em->flush();
        }

        return $this->redirectToRoute("admin_tag_index");
    }

}