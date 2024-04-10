<?php

namespace App\Controller;

use App\Entity\Exposition;
use App\Entity\Reservation;
use App\Entity\User;

use App\Form\ExpositionType;
use App\Repository\ExpositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/client')]
class ClientExpo extends AbstractController
{
//hethi  lel accueil

    #[Route('/getall', name: 'app_client_getall', methods: ['GET'])]
    public function getall(ExpositionRepository $expositionRepository): Response
    {
        $expositions = $expositionRepository->findAll();
    
        return $this->render('client/watch.html.twig', [
            'expositions' => $expositions,
        ]);
    }


//hethi  lel lista

#[Route('/getallExpo', name: 'app_client_getallExpo', methods: ['GET'])]
    public function getallExpoClient(Request $request,ExpositionRepository $expositionRepository,PaginatorInterface $paginator): Response
    {
        $expositions = $expositionRepository->findAll();
        // Pagination logic
    $currentPage = $request->query->getInt('page', 1); // Get the current page number (default to 1)
    $perPage = 6; // Number of expositions per page (adjust as needed)

    $paginatedExpositions = $paginator->paginate(
        $expositions,
        $currentPage,
        $perPage
    );

        return $this->render('client/liste_expo.html.twig', [
            'expositions' => $paginatedExpositions,
            'knp_pagination' => $paginatedExpositions, 
        ]);
    }
    

    #[Route('/search', name: 'exposition_search', methods: ['GET'])]
    public function search(Request $request, ExpositionRepository $expositionRepository): Response
    {
        $query = $request->query->get('q');
        
    
        $expositions = $expositionRepository->searchByName($query);
    
        // Serialize the result to JSON and return the response
        $jsonData = $this->serializeExpositions($expositions);
    
        return new JsonResponse($jsonData);
    }
    private function serializeExpositions($expositions)
{
    $jsonData = [];

    foreach ($expositions as $exposition) {
        $jsonData[] = [
            'idExposition' => $exposition->getIdExposition(),
            'nom' => $exposition->getNom(),
            'theme' => $exposition->getTheme(),
            'image' => $exposition->getImage(),

            'dateDebut' => $exposition->getDateDebut()->format('Y-m-d'), // Format dateDebut
            'dateFin' => $exposition->getDateFin()->format('Y-m-d'), // Format dateFin
            'heureDebut' => $exposition->getHeureDebut()->format('H:i'), // Format heureDebut
            'heureFin' => $exposition->getHeureFin()->format('H:i'),
        ];
    }

    return $jsonData;
}
    

    #[Route('/user-images/{imageName}', name: 'user_images')]
    public function getUserImage(string $imageName): Response
    {

        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    }

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/{idExposition}', name: 'app_client_show', methods: ['GET'])]
    public function show(Request $request, Exposition $exposition): Response
    {
        // Check if the user has already made a reservation for this exposition
        $userId = 6; // Assuming user ID is 6
        $reservationRepository = $this->entityManager->getRepository(Reservation::class);

        
        // Querying based on the conditions
        $reservationExists = $reservationRepository->findOneBy([
            'user' => $userId,
            'exposition' => $exposition,
            'accessByAdmin' => [0, 1] // Assuming accessByAdmin can only be 0 or 1
        ]);
    
        return $this->render('client/show.html.twig', [
            'exposition' => $exposition,
            'reservationExists' => $reservationExists,
        ]);
    }
    
    


   

   
    
    #[Route('/reserve/{idExposition}', name: 'app_client_reserve', methods: ['POST'])]
    public function reserve(Request $request, Exposition $exposition): Response
    {
        // Retrieve the number of tickets from the submitted form data
        $ticketsNumber = $request->request->get('ticketsNumber');
    
        // Validate if the number of tickets is valid (you can add more validation as needed)
        if (!is_numeric($ticketsNumber) || $ticketsNumber <= 0) {
            // Handle invalid input (e.g., display an error message)
            $this->addFlash('error', 'Invalid number of tickets.');
            return $this->redirectToRoute('app_client_getallExpo');
        }
    
        // Set the user ID to 6
        $userId = 6;
    
        // Fetch the User entity using the user ID
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($userId);
    
        // Create a new Reservation entity
        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setExposition($exposition);
        $reservation->setTicketsNumber($ticketsNumber); // Set the number of tickets
        $reservation->setAccessByAdmin(0); // Access by admin is set to 0
    
        // Set the reservation date
        $reservation->setDateReser(new \DateTime());
    
        try {
            // Persist the reservation
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            
            // Redirect the user after successful reservation
            $this->addFlash('success', 'Reservation successful.');
            return $this->redirectToRoute('app_client_getallExpo');
        } catch (\Exception $e) {
            // Handle exception, if any
            $this->addFlash('error', 'Failed to reserve. Please try again.');
            return $this->redirectToRoute('app_client_getallExpo');
        }
    }
    


    


   
  

    

  
}
