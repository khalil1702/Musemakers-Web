<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\Cours1Type;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'app_cours_index', methods: ['GET', 'POST'])]
    public function index(CoursRepository $coursRepository, Request $request): Response
    {
  // Récupérer les paramètres de tri et de recherche depuis la requête
  $sortBy = $request->query->get('sort_by', 'default_field'); // Champ de tri par défaut
  $searchTerm = $request->query->get('search', '');

  // Utiliser ces paramètres pour récupérer les cours correspondants depuis le repository
  $cours = $coursRepository->findFilteredAndSorted($sortBy, $searchTerm);

  return $this->render('cours/index.html.twig', [
      'cours' => $cours,
      'searchTerm' => $searchTerm,
      'sortBy' => $sortBy,
  ]);
  
        return $this->render('cours/index.html.twig', [
            'cours' => $cours,
            'searchTerm' => $searchTerm,
        ]);
    
    }
    #[Route('/front', name: 'app_cours_index1', methods: ['GET'])]
    public function index2(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/indexfront.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }
   

    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cour = new Cours();
       
        $form = $this->createForm(Cours1Type::class, $cour);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($cour).die();
            $entityManager->persist($cour);
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{idCours}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {
        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{idCours}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Cours1Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{idCours}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getIdCours(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
   
}
