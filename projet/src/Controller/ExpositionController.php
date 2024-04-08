<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Entity\Exposition;
use App\Entity\Reservation;
use App\Entity\User;
use App\Form\ExpositionType;
use App\Repository\ExpositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/exposition')]
class ExpositionController extends AbstractController
{

  //////////////
//only this work
#[Route('/', name: 'app_exposition_index', methods: ['GET'])]
public function index(Request $request, ExpositionRepository $expositionRepository, PaginatorInterface $paginator): Response
{
    // Get all expositions
    $expositions = $expositionRepository->findAll();

    // Pagination logic
    $currentPage = $request->query->getInt('page', 1); // Get the current page number (default to 1)
    $perPage = 5; // Number of expositions per page (adjust as needed)

    $paginatedExpositions = $paginator->paginate(
        $expositions,
        $currentPage,
        $perPage
    );

    // Render the template with paginated data
    return $this->render('exposition/index.html.twig', [
        'expositions' => $paginatedExpositions,
        'knp_pagination' => $paginatedExpositions, // Pass the pagination object to Twig
    ]);
}

    
    #[Route('/user-images/{imageName}', name: 'user_images')]
public function getUserImage(string $imageName): Response
{
    $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
    
    // Return the image as a response
    return new BinaryFileResponse($imagePath);
}

#[Route('/new', name: 'app_exposition_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $exposition = new Exposition();
    $form = $this->createForm(ExpositionType::class, $exposition);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        // Check if an image file has been uploaded
        if ($imageFile) {
            // Generate a unique filename
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the desired directory
            try {
                $imageFile->move(
                    'C:/xampp/htdocs/user_images',
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle the exception if unable to move the file
                // For example, log an error message or display a flash message
                // and return a response to inform the user of the error
                // Note: You may need to handle this differently based on your application's requirements
                return $this->redirectToRoute('app_exposition_index', [
                    'error' => 'Failed to upload the image file.'
                ]);
            }

            // Update the 'image' property of the Exposition entity with the new filename
            $exposition->setImage($newFilename);
        }

        // Persist the entity
        $entityManager->persist($exposition);
        $entityManager->flush();

        return $this->redirectToRoute('app_exposition_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('exposition/new.html.twig', [
        'exposition' => $exposition,
        'form' => $form,
    ]);
}


    #[Route('/{idExposition}', name: 'app_exposition_show', methods: ['GET'])]
    public function show(Exposition $exposition): Response
    {
        return $this->render('exposition/show.html.twig', [
            'exposition' => $exposition,
        ]);
    }

    #[Route('/{idExposition}/edit', name: 'app_exposition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exposition $exposition, EntityManagerInterface $entityManager): Response
{
    // Récupérer le chemin de l'image existante
    $imagePath = $exposition->getImage();

    $form = $this->createForm(ExpositionType::class, $exposition);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();

        // Vérifier si un nouveau fichier d'image est téléchargé
        if ($imageFile) {
            // Générer un nom de fichier unique
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Déplacer le fichier vers le répertoire souhaité
            try {
                $imageFile->move(
                    'C:/xampp/htdocs/user_images',
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'exception si le fichier ne peut pas être déplacé
                return $this->redirectToRoute('app_exposition_index', [
                    'error' => 'Failed to upload the image file.'
                ]);
            }

            // Mettre à jour la propriété 'image' de l'entité Exposition avec le nouveau nom de fichier
            $exposition->setImage($newFilename);
        } else {
            // Si aucun fichier n'est téléchargé, conserver l'image existante
            $exposition->setImage($imagePath);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_exposition_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('exposition/edit.html.twig', [
        'exposition' => $exposition,
        'form' => $form,
    ]);
}

    
    

    
    


    #[Route('/{idExposition}', name: 'app_exposition_delete', methods: ['POST'])]
    public function delete(Request $request, Exposition $exposition, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exposition->getIdExposition(), $request->request->get('_token'))) {
            $entityManager->remove($exposition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exposition_index', [], Response::HTTP_SEE_OTHER);
    }

    // #[Route('/reserve/{id}', name: 'app_exposition_reserve', methods: ['POST'])]
    // public function reserve(Request $request, int $id, EntityManagerInterface $entityManager): Response
    // {
    //     // Find the exposition by ID
    //     $exposition = $entityManager->getRepository(Exposition::class)->find($id);

    //     // Assuming the user ID is 6
    //     $userId = 6;

    //     // Create a new reservation
    //     $reservation = new Reservation();
    //     $reservation->setExposition($exposition);
    //     $reservation->setUser($entityManager->getReference(User::class, $userId)); // Assuming User::class is the entity for your User
    //     $reservation->setTicketsNumber(1); // Assuming one ticket is reserved
    //     $reservation->setAccessByAdmin(0); // Set accessByAdmin to 0

    //     // Persist and flush the reservation
    //     $entityManager->persist($reservation);
    //     $entityManager->flush();

    //     // Redirect back to the exposition index
    //     return $this->redirectToRoute('app_exposition_index');
    // }




}
