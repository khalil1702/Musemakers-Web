<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\Cours1Type;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'app_cours_index', methods: ['GET', 'POST'])]
    public function index(CoursRepository $coursRepository, Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Récupérer les paramètres de tri et de recherche depuis la requête
        $sortBy = $request->query->get('sort_by', 'default_field'); // Champ de tri par défaut
        $searchTerm = $request->query->get('search', '');

        // Utiliser ces paramètres pour récupérer les cours correspondants depuis le repository
        $cours = $coursRepository->findFilteredAndSorted($sortBy, $searchTerm);

        // Pagination logic
        $currentPage = $request->query->getInt('page', 1);
        $perPage = 5;

        $paginatedCours = $paginator->paginate(
            $cours,
            $currentPage,
            $perPage
        );

        // Passer la variable 'knp_pagination' dans le contexte de rendu
        // pour qu'elle soit disponible dans le template Twig
        return $this->render('cours/index.html.twig', [
            'cours' => $paginatedCours,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'knp_pagination' => $paginatedCours,
        ]);
    }
    #[Route('/front', name: 'app_cours_index1', methods: ['GET'])]
    public function index2(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/indexfront.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }
    #[Route('/front/fav', name: 'app_cours_fav', methods: ['GET'])]
    public function favoriteCoursesPage(): Response
    {
        return $this->render('cours/coursFavories.html.twig');
    }


    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SendMailService $emailService, UserRepository $userRepository): Response
    {
        $cour = new Cours();
        $users = $userRepository->findLimitedUsers(4);
        $form = $this->createForm(Cours1Type::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cour);
            $entityManager->flush();

            if ($users != 0) {

                foreach ($users as $user) {
                    $emailService->send(
                        'Musemakers@gmail.com',
                        $user->getEmail(),
                        'New Cour',
                        'courInEmail',
                        [
                            'user' => $user,
                            'cour' => $cour
                        ]
                    );
                }
            }

            return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }
    #[Route('/get-favorite-courses', name: 'app_cours_getfav', methods: ['POST'])]
    public function getFavoriteCourses(Request $request, LoggerInterface $logger,CoursRepository $coursRepository): JsonResponse
    {
        $favoriteCourseIds = json_decode($request->getContent(), true);
    
        // Log the favorite course IDs
        $logger->info('Favorite course IDs:', $favoriteCourseIds);
    
        $favoriteCourses = $coursRepository->findBy(['idCours' => $favoriteCourseIds]);
    
        // Log the retrieved favorite courses
        $logger->info('Favorite courses:', $favoriteCourses);
    
        // Serialize the favorite courses to JSON
        $favoriteCoursesJson = [];
        foreach ($favoriteCourses as $course) {
            $favoriteCoursesJson[] = [
                'id' => $course->getIdCours(),
                'title' => $course->getTitreCours(),
                'description' => $course->getDescriCours(),
            ];
        }
    
        return new JsonResponse($favoriteCoursesJson);
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
        if ($this->isCsrfTokenValid('delete' . $cour->getIdCours(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
