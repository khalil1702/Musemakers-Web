<?php

namespace App\Controller;

use App\Entity\Exposition;
use App\Entity\Reservation;
use App\Entity\User;

use App\Form\ExpositionType;
use App\Repository\ExpositionRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/admin')]
class AdminReser extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/requests', name: 'admin_reservation_requests')]
public function reservationRequests(Request $request, ReservationRepository $reservationRepository, PaginatorInterface $paginator): Response
{
    $reservationRequests = $reservationRepository->findBy(['accessByAdmin' => 0]);

    $reservationRequests = $paginator->paginate(
        $reservationRequests,
        $request->query->getInt('page', 1), // Get the current page number
        5 // Number of items per page
    );
    return $this->render('back_office/reservation_requests.html.twig', [
        'reservationRequests' => $reservationRequests,
        'knp_pagination' => $reservationRequests

    ]);
} 


    #[Route('/all-reservations', name: 'admin_all_reservations')]
    public function allReservations(Request $request, ReservationRepository $reservationRepository, PaginatorInterface $paginator): Response
    {
        // Retrieve all reservations from the repository
        $reservationsQuery = $reservationRepository->findAll();
    
        $knp_pagination = $paginator->paginate(
            $reservationsQuery,
            $request->query->getInt('page', 1),
            5
        );
    
        return $this->render('back_office/all_reservations.html.twig', [
            'reservations' => $knp_pagination, //lista
        'knp_pagination' => $knp_pagination
        ]);
    }


    #[Route('/accept-reservation/{id}', name: 'admin_accept_reservation')]
    public function acceptReservation(Reservation $reservation): Response
    {
        $reservation->setAccessByAdmin(1);
        $this->entityManager->flush();

        $this->addFlash('success', 'Reservation accepted.');
        return $this->redirectToRoute('admin_reservation_requests');
    }

    #[Route('/reject-reservation/{id}', name: 'admin_reject_reservation')]
    public function rejectReservation(Reservation $reservation): Response
    {
        $reservation->setAccessByAdmin(2);
        $this->entityManager->flush();

        $this->addFlash('danger', 'Reservation rejected.');
        return $this->redirectToRoute('admin_reservation_requests');
    }

////historique reservation client
#[Route('/histo', name: 'app_client_getreser', methods: ['GET'])]
public function getReservations(Request $request, PaginatorInterface $paginator): Response
{
    $userId = 6; // ID of the client

    $userRepository = $this->entityManager->getRepository(User::class);
    $user = $userRepository->find($userId);

    // Fetch all reservations for the user
    $reservationsQuery = $user->getReservations();

    $knp_pagination = $paginator->paginate(
        $reservationsQuery,
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('client/histo_reser.html.twig', [
        'reservations' => $knp_pagination,
        'knp_pagination' => $knp_pagination
    ]);
}
#[Route('/edit-reservation-tickets/{id}', name: 'app_client_edit_reservation_tickets', methods: ['POST'])]
public function editReservationTickets(Request $request, Reservation $reservation): Response
{
    $newTicketsNumber = $request->request->get('ticketsNumber');
    
    // Update the ticketsNumber for the reservation
    $reservation->setTicketsNumber($newTicketsNumber);
    $this->entityManager->flush();
    
    $this->addFlash('success', 'Tickets number updated successfully.');
    return $this->redirectToRoute('app_client_getreser');
}


    #[Route('/cancel-reservation/{id}', name: 'app_client_cancel_reservation', methods: ['GET'])]
public function cancelReservation(Reservation $reservation): Response
{
    // Set accessByAdmin to 3
    $reservation->setAccessByAdmin(3);
    
    // Update the reservation
    $this->entityManager->flush();
    
    // Redirect the user after cancelling the reservation
    $this->addFlash('success', 'Reservation cancelled.');
    return $this->redirectToRoute('app_client_getreser');
}



    

  
}
