<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditType;
use App\Form\NewPasswordType;
use App\Form\ResetPassType;
use App\Form\User1Type;
use App\Form\User2Type;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Swift_Mailer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/user')]
class UserController extends AbstractController
{
  
    #[Route("/oubli_pass", name: "forgotten_password")]
    public function forgottenPass(
        Request $request, 
        UserRepository $userRepository, 
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        // Création du formulaire
        $form = $this->createForm(ResetPassType::class);
        
        // Traitement du formulaire
        $form->handleRequest($request);
    
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $formData = $form->getData();
            $email = $form->get('email')->getData();
    
            // Recherche de l'utilisateur par son adresse email
            $user = $userRepository->findOneBy(['email' => $email]);
    
            // Si l'utilisateur existe
            if ($user) {
                // Génération du token
                $tokenData = ['email' => $email];
                $token = base64_encode(json_encode($tokenData));
    
                // Stockage du token dans un cookie
                $response = new Response();
                $response->headers->setCookie(
                    new Cookie('reset_password_token', $token)
                );
                $response->send();
    
                // Génération de l'URL de réinitialisation du mot de passe
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
    
                // Création du message avec l'URL de réinitialisation du mot de passe
                $email = (new Email())
                    ->from('yassinetounsi925@gmail.com') // Remplacez par votre adresse e-mail
                    ->to($email)
                    ->subject('Réinitialisation de mot de passe')
                    ->html("<p>Bonjour,</p><p>Vous avez demandé une réinitialisation de mot de passe. Cliquez sur le lien suivant pour procéder à la réinitialisation : <a href='$resetUrl'>Réinitialiser le mot de passe</a></p>");
    
                    
                // Création du transport
                $transport = Transport::fromDsn('smtp://gofitpro8@gmail.com:czrr%20mudh%20itak%20iwhy@smtp.gmail.com:587');
    
                // Création du mailer
                $mailer = new Mailer($transport);
    
                // Envoi de l'e-mail
                $mailer->send($email);
    
                // Message flash pour indiquer que l'e-mail de réinitialisation a été envoyé
                $this->addFlash('success', 'Un e-mail de réinitialisation de mot de passe a été envoyé à votre adresse.');
    
                // Redirection vers la page de connexion
                return $this->redirectToRoute('app_login');
            } else {
                // Message flash si l'email n'est pas trouvé
                $this->addFlash('danger', 'Aucun utilisateur trouvé avec cette adresse email.');
    
                // Redirection vers la page de demande d'email
                return $this->redirectToRoute('forgotten_password');
            }
        }
    
        // Affichage du formulaire
        return $this->render('user/forgotten_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route("/reset_pass", name:"app_reset_password")]
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session): Response
    {
        $resetToken = $request->cookies->get('reset_password_token');

        if (!$resetToken) {
            $this->addFlash('danger', 'Token de réinitialisation de mot de passe invalide.');
            return $this->redirectToRoute('app_login');
        }

        $tokenData = json_decode(base64_decode($resetToken), true);
        $email = isset($tokenData['email']) ? $tokenData['email'] : null;

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mdp = $form->get('password')->getData();

            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $encodedPassword = $passwordEncoder->encodePassword($user, $mdp);
                $user->setMdp($encodedPassword);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // Supprimer le cookie de réinitialisation de mot de passe
                $response = new Response();
                $response->headers->clearCookie('reset_password_token');
                $response->sendHeaders();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Utilisateur introuvable.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
            'token' => $resetToken,
            'email' => $email,
        ]);
    }
        
  

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
{
    $nomUser = $request->query->get('nomUser');
    $prenomUser = $request->query->get('prenomUser');
    $email = $request->query->get('email');
    $numTel = $request->query->get('numTel');

    $usersQuery = $userRepository->search($nomUser, $prenomUser, $email, $numTel);

    // Paginate the results of the query
    $users = $paginator->paginate(
        // Doctrine Query, not results
        $usersQuery,
        // Define the page parameter
        $request->query->getInt('page', 1),
        // Items per page
        10
    );

    // If it's an AJAX request, return the search results directly
    if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
            'users' => $users,
            // Add any other data you want to return
        ]);
    }

    // Get the gender statistics
    $stats = $userRepository->getUserGenderStats();

    // Render the template and pass the statistics
    return $this->render('user/indexartiste.html.twig', [
        'users' => $users,
        'stats' => $stats,  // Pass the stats to your template
    ]);
}
#[Route('/search', name: 'user_search', methods: ['GET'])]
public function search(Request $request, UserRepository $userRepository): Response
{
    $query = $request->query->get('q');

    $users = $userRepository->searchByName($query);

    return $this->render('user/search_user.html.twig', [
        'users' => $users,
    ]);
}
#[Route('/block/{idUser}', name: 'app_user_block', methods: ['POST'])]
public function blockUser(User $user): Response
{
    // Récupérer l'identifiant de l'utilisateur
    $userId = $user->getIdUser();

    // Vérifier si l'utilisateur est déjà bloqué
    if ($this->isUserBlocked($user)) {
        // Si l'utilisateur est déjà bloqué, ne rien faire
        return new Response('User is already blocked.', Response::HTTP_BAD_REQUEST);
    }

    // Ajouter l'identifiant de l'utilisateur bloqué dans le fichier .txt
    $this->addToBlacklist($userId);

    // Rediriger l'administrateur vers la page de gestion des utilisateurs
    return $this->redirectToRoute('app_user_index');
}

#[Route('/user/unblock/{idUser}', name: 'app_user_unblock')]
public function unblockUser($idUser): Response
{
    // Récupérer l'identifiant de l'utilisateur à débloquer

    // Définir le chemin absolu vers le fichier de liste noire
    $blacklistFile = realpath(__DIR__ . '/../../public/blacklist.txt');

    // Récupérer la liste des identifiants bloqués depuis le fichier
    $blacklistedIds = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Supprimer l'identifiant de l'utilisateur de la liste noire
    $key = array_search($idUser, $blacklistedIds);
    if ($key !== false) {
        unset($blacklistedIds[$key]);
    }

    // Écrire la nouvelle liste dans le fichier
    file_put_contents($blacklistFile, implode(PHP_EOL, $blacklistedIds));

    // Rediriger vers une page de confirmation ou une autre page appropriée
    return $this->redirectToRoute('app_user_index');
}

// Méthode pour ajouter l'identifiant de l'utilisateur bloqué dans le fichier .txt
private function addToBlacklist(int $userId): void
{
    // Définir le chemin du fichier .txt
    $blacklistFile = 'blacklist.txt';

    // Ajouter l'identifiant de l'utilisateur bloqué dans le fichier
    file_put_contents($blacklistFile, $userId . PHP_EOL, FILE_APPEND);
}

public function isUserBlocked(User $user): bool
{
    if ($user === null) {
        return false; // Ou une autre logique selon vos besoins
    }
    // Définir le chemin absolu vers le fichier de liste noire
    $blacklistFile = realpath(__DIR__ . '/../../public/blacklist.txt'); 

    // Récupérer la liste des identifiants bloqués depuis le fichier
    $blacklistedIds = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Vérifier si l'ID de l'utilisateur est dans la liste des identifiants bloqués
    $isBlocked = in_array($user->getIdUser(), $blacklistedIds);

    var_dump($isBlocked); // Ajout pour le débogage

    return $isBlocked;
}



    
    
    
    #[Route('/clients', name: 'app_user_indexclients', methods: ['GET'])]
    public function index2(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/user-images/{imageName}', name: 'user_images')]
    public function getUserImage(string $imageName): Response
    {
        $imagePath = 'C:\xampp\htdocs\user_images\\' . $imageName;
        
        // Return the image as a response
        return new BinaryFileResponse($imagePath);
    } 
    
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setMdp(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('mdp')->getData()
                )
            );
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
                    return $this->redirectToRoute('app_user_index', [
                        'error' => 'Failed to upload the image file.'
                    ]);
                }
    
                // Update the 'image' property of the User entity with the new filename
                $user->setImage($newFilename);
            }
    
            // Persist the entity
            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/editProfile', name: 'editProfile')]
    public function editProfile(UserRepository $userRepository, ManagerRegistry $managerRegistry, Request $request): Response
    {
        $m=$managerRegistry->getManager();
        $findid=$this->getUser();
        $form=$this->createForm(EditType::class,$findid);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $m->persist($findid);
            $m->flush();

            return $this->redirectToRoute('app_user_indexclients');
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    

    #[Route('/{idUser}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{idUser}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le chemin de l'image existante
        $imagePath = $user->getImage();
    
        $form = $this->createForm(User2Type::class, $user);
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
                    return $this->redirectToRoute('app_user_index', [
                        'error' => 'Failed to upload the image file.'
                    ]);
                }
    
                // Mettre à jour la propriété 'image' de l'entité User avec le nouveau nom de fichier
                $user->setImage($newFilename);
            } else {
                // Si aucun fichier n'est téléchargé, conserver l'image existante
                $user->setImage($imagePath);
            }
    
            // Flush les changements à la base de données
            $entityManager->flush();
    
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{idUser}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getIdUser(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
   

}
