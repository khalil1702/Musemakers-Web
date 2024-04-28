<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RennitialisationController extends AbstractController
{
    private $mailer;
    private $tokenGenerator;

    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
    }

    #[Route('/forgot-password1', name: 'app_forgot_password')]
    public function requestPasswordReset(Request $request)
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);

            // Ensure the user exists in the database
            if (!$user) {
                throw $this->createNotFoundException('No user found for email ' . $form->get('email')->getData());
            }

            // Generate a unique token
            $token = $this->tokenGenerator->generateToken();

            // Set the token on the user and save the user
            $user->setResetToken($token);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // Create the email
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@yourdomain.com', 'No Reply'))
                ->to($user->getEmail())
                ->subject('Your password reset request')
                ->htmlTemplate('reset_password/email.html.twig')
                ->context([
                    'token' => $token,
                    'url' => $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            // Send the email
            $this->mailer->send($email);

            // Redirect to a route that informs the user to check their email
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

   
}
