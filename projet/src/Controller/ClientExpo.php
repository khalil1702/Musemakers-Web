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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
public function getallExpoClient(Request $request, ExpositionRepository $expositionRepository, PaginatorInterface $paginator): Response
{
    $userId = 6; // Assuming user ID is 6

    $expositions = $expositionRepository->findAll();

    // Pagination logic
    $currentPage = $request->query->getInt('page', 1); // Get the current page number (default to 1)
    $perPage = 6; // Number of expositions per page (adjust as needed)

    $paginatedExpositions = $paginator->paginate(
        $expositions,
        $currentPage,
        $perPage
    );

    // Retrieve rating from session for each exposition
    $ratings = [];
    foreach ($expositions as $exposition) {
        $rating = $this->session->get('ratingUser_' . $userId . '_exposition_' . $exposition->getIdExposition(), null);
        $ratings[$exposition->getIdExposition()] = $rating;
    }

    return $this->render('client/liste_expo.html.twig', [
        'expositions' => $paginatedExpositions,
        'knp_pagination' => $paginatedExpositions,
        'ratings' => $ratings // Pass ratings to the template
    ]);
}


#[Route('/search', name: 'exposition_search', methods: ['GET'])]
public function search(Request $request, ExpositionRepository $expositionRepository): Response
{
    $userId = 6; // Assuming user ID is 6

    // Fetch all expositions
    $expositions = $expositionRepository->findAll();

    // Initialize an empty array to store ratings
    $ratings = [];

    // Loop through each exposition to retrieve its rating
    foreach ($expositions as $exposition) {
        // Get the rating from session storage based on user ID and exposition ID
        $rating = $this->session->get('ratingUser_' . $userId . '_exposition_' . $exposition->getIdExposition(), null);
        
        // Store the rating in the ratings array with the exposition ID as the key
        $ratings[$exposition->getIdExposition()] = $rating;
    }

    // Retrieve query parameters
    $query = $request->query->get('q');
    $theme = $request->query->get('theme');

    // Perform search based on query and theme
    $expositions = $expositionRepository->searchByNameAndTheme($query, $theme);

    // Render the search results template with expositions, user ID, and ratings
    return $this->render('client/search_expo.html.twig', [
        'expositions' => $expositions,
        'idUser' => $userId,
        'ratings' => $ratings,
    ]);
}

#[Route('/share', name: 'app_share', methods: ['GET'])]
public function share(Request $request)
{
    $expositionId = $request->query->get('id');

    // Fetch the exposition from the database using the id
    $repository = $this->getDoctrine()->getRepository(Exposition::class);
    $exposition = $repository->find($expositionId);

    if (!$exposition) {
        // Handle error here
        throw $this->createNotFoundException('No exposition found for id '.$expositionId);
    }

    // Create the message you want to share
    $message = 'Regardez cette image de l\'œuvre numéro ' . $exposition->getNom();

    $url = 'https://www.facebook.com/dialog/feed?app_id=YOUR_APP_ID&display=popup&caption=' . urlencode($message) . '&link=https://yourwebsite.com/exposition/&redirect_uri=https://yourwebsite.com/exposition/';

    return new RedirectResponse($url);
}
  
  

    #[Route('/user-images/{imageName}', name: 'user_images')]
    public function getUserImage(string $imageName): Response
    {

        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    }

    private $entityManager;
    private $session;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;

    }

    #[Route('/save-rating', name: 'app_rate', methods: ['POST'])]
public function saveRating(Request $request): JsonResponse
{
    // Get the exposition ID and rating from the request
    $data = json_decode($request->getContent(), true);
    $expositionId = $data['expositionId'];
    $rating = $data['rating'];

    // Save the rating to the session
    $userId = 6; // Assuming user ID is 6
    $this->session->set('ratingUser_' . $userId . '_exposition_' . $expositionId, $rating);

    return new JsonResponse(['success' => true]);
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
        
        // Get rating from session if exists
        $rating = $this->session->get('ratingUser_' . $userId . '_exposition_' . $exposition->getIdExposition(), null);
        
        return $this->render('client/show.html.twig', [
            'exposition' => $exposition,
            'reservationExists' => $reservationExists,
            'rating' => $rating, // Pass rating to the template
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
