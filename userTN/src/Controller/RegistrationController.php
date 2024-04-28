<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginformauthentificatorAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use PharIo\Manifest\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Constraints\Email as ConstraintsEmail;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginformauthentificatorAuthenticator $authenticator, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, AuthenticationUtils $authenticationUtils): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setMdp(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
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
                // ... handle exception if something happens during file upload
            }
    
            // Set the path of the image in your User entity
            $user->setImage($newFilename);
        }
            
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Generate email verification signature
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getIdUser(),
                $user->getEmail(),
                ['id' => $user->getIdUser()]
            );
    
            // Create the Transport
            $transport = Transport::fromDsn('smtp://gofitpro8@gmail.com:czrr%20mudh%20itak%20iwhy@smtp.gmail.com:587');
    
            // Create a Mailer object
            $mailer = new Mailer($transport);
    
            // Create the email
            $email = (new MimeEmail())
            ->from(new Address(
                'yassinetounsi925@gmail.com',
                'Go Fit Pro'
            ))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->html('<p>Thank you for registering on our site. Please confirm your email by clicking on the link below:</p><a href="' . $signatureComponents->getSignedUrl() . '">Confirm my email</a>');
    
            // Send the email
            $mailer->send($email);
    
            // do anything else you need here, like send an email
    
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
            
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('idUser');

        if (null === $id) {
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
