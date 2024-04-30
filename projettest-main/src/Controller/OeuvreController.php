<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreType;
use App\Repository\AvisRepository;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sbyaute\StarRatingBundle\Form\StarRatingType;


#[Route('/o')]
class OeuvreController extends AbstractController
{
     
    #[Route('/rechercheoeuvre', name: 'oeuvre_search1', methods: ['GET'])]

    public function search(Request $request, OeuvreRepository $oeuvreRepository , AvisRepository $avisRepository): Response
    {
        $query = $request->query->get('q');
    
        $oeuvres = $oeuvreRepository->searchByName($query);
        $averageRatings = [];
    
        // Parcourir chaque œuvre pour calculer la moyenne des notes
        foreach ($oeuvres as $oeuvre) {
            // Récupérer les avis correspondant à l'œuvre
            $avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);
            
            // Initialiser la somme des notes et le nombre d'avis
            $totalRating = 0;
            $numberOfRatings = count($avis);
            
            // Calculer la somme des notes
            foreach ($avis as $avi) {
                $totalRating += $avi->getNote();
            }
            
            // Calculer la moyenne des notes
            $averageRating = $numberOfRatings > 0 ? $totalRating / $numberOfRatings : 0;
            
            // Ajouter la moyenne des notes à chaque œuvre
            $averageRatings[$oeuvre->getIdOeuvre()] = $averageRating;
        }

        return $this->render('oeuvre/search_results.html.twig', [
            'oeuvres' => $oeuvres,
            'averageRatings' => $averageRatings,
        ]);
    }

    #[Route('/sort1', name: 'oeuvre_sort1', methods: ['GET'])]
    public function sort(Request $request, OeuvreRepository $oeuvreRepository, AvisRepository $avisRepository): Response
    {
        // Récupérer les critères de tri depuis la requête
        $sortBy = $request->query->get('sort_by');
        $sortOrder = $request->query->get('sort_order');
    
        if ($sortBy && $sortOrder) {
            // Trier les œuvres en fonction des critères sélectionnés
            $criteria = [$sortBy => $sortOrder];
            $oeuvres = $oeuvreRepository->findBy([], $criteria);
        } else {
            // Par défaut, ne pas appliquer de tri et récupérer les œuvres dans l'ordre initial
            $oeuvres = $oeuvreRepository->findAll();
        }
    
        $averageRatings = [];
        
        // Parcourir chaque œuvre pour calculer la moyenne des notes
        foreach ($oeuvres as $oeuvre) {
            // Récupérer les avis correspondant à l'œuvre
            $avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);
            
            // Initialiser la somme des notes et le nombre d'avis
            $totalRating = 0;
            $numberOfRatings = count($avis);
            
            // Calculer la somme des notes
            foreach ($avis as $avi) {
                $totalRating += $avi->getNote();
            }
            
            // Calculer la moyenne des notes
            $averageRating = $numberOfRatings > 0 ? $totalRating / $numberOfRatings : 0;
            
            // Ajouter la moyenne des notes à chaque œuvre
            $averageRatings[$oeuvre->getIdOeuvre()] = $averageRating;
        }
    
        return $this->render('oeuvre/index.html.twig', [
            'oeuvres' => $oeuvres,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'averageRatings' => $averageRatings,
        ]);
    }
    

  

     #[Route('/afficher', name: 'app_oeuvre_index', methods: ['GET'])]
     public function index(OeuvreRepository $oeuvreRepository): Response
     {
        $oeuvres = $oeuvreRepository->findAll();
         return $this->render('oeuvre/index.html.twig', [
            'oeuvres' => $oeuvres,
        ]);
    }

    #[Route('/new', name: 'app_oeuvre_new', methods: ['GET', 'POST'])]
     public function new(Request $request, EntityManagerInterface $entityManager): Response
   {
    $oeuvre = new Oeuvre();
    $form = $this->createForm(OeuvreType::class, $oeuvre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        // Check if an image file has been uploaded
        if ($imageFile) {
            // Use the original filename
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $originalFilename.'.'.$imageFile->guessExtension();

            // Move the file to the desired directory
            try {
                $imageFile->move(
                    'C:/xampp/htdocs/user_images',
                    $newFilename
                );
            } catch (FileException $e) {
                // Add a flash message to inform the user about the error
                $this->addFlash('error', 'Failed to upload the image file.');

                // Redirect to the index page
                return $this->redirectToRoute('app_oeuvre_index');
            }

            // Update the 'image' property of the Oeuvre entity with the new filename
            $oeuvre->setImage($newFilename);
        }

        $entityManager->persist($oeuvre);
        $entityManager->flush();

        return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
    }

    // Render the form in all other cases
    return $this->renderForm('oeuvre/new.html.twig', [
        'oeuvre' => $oeuvre,
        'form' => $form,
    ]);
}


    #[Route('/{idOeuvre}', name: 'app_oeuvre_show', methods: ['GET'])]
    public function show(Oeuvre $oeuvre): Response
    {
        return $this->render('oeuvre/show.html.twig', [
            'oeuvre' => $oeuvre,
        ]);
    }

    #[Route('/{idOeuvre}/edit', name: 'app_oeuvre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Oeuvre $oeuvre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OeuvreType::class, $oeuvre);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            // Check if an image file has been uploaded
            if ($imageFile) {
                // Use the original filename
                $newFilename = $imageFile->getClientOriginalName();
    
                // Move the file to the desired directory
                try {
                    $imageFile->move(
                        'C:/xampp/htdocs/user_images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Add a flash message to inform the user about the error
                    $this->addFlash('error', 'Failed to upload the image file.');
    
                    // Redirect to the index page
                    return $this->redirectToRoute('app_oeuvre_index');
                }
    
                // Update the 'image' property of the Oeuvre entity with the new filename
                $oeuvre->setImage($newFilename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('oeuvre/edit.html.twig', [
            'oeuvre' => $oeuvre,
            'form' => $form,
        ]);
    }
    

    #[Route('/{idOeuvre}', name: 'app_oeuvre_delete', methods: ['POST'])]
    public function delete(Request $request, Oeuvre $oeuvre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$oeuvre->getIdOeuvre(), $request->request->get('_token'))) {
            $entityManager->remove($oeuvre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user-images/{imageName}', name: 'user_images')]
    public function getUserImage(string $imageName): Response
    {
        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    } 
    
    #[Route('/{idOeuvre}/avis', name: 'app_oeuvre_avis', methods: ['GET'])]
    public function showAvis(Oeuvre $oeuvre, AvisRepository $avisRepository): Response
    {
        // Récupérez les avis associés à cette œuvre spécifique
$avis = $avisRepository->findBy(['oeuvre' => $oeuvre->getIdOeuvre()]);

        
        return $this->render('oeuvre/avisadmin.html.twig', [
            'oeuvre' => $oeuvre,
            'avis' => $avis,
        ]);
    }

 

 
}
