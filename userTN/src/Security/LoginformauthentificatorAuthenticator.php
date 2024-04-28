<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginformauthentificatorAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Récupérer l'utilisateur
        $user = $token->getUser();

        // Vérifier si l'utilisateur est bloqué
        if ($this->isUserBlocked($user)) {
            // Invalider la session
            $request->getSession()->set('error', 'Votre compte a été bloqué.');

            $request->getSession()->invalidate();


            // Refuser la connexion et afficher un message d'erreur
            throw new CustomUserMessageAuthenticationException('Votre compte a été bloqué.');
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
    
        // Récupérer les rôles de l'utilisateur
        $roles = $token->getUser()->getRoles();
    
        // Rediriger en fonction du rôle
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_user_index'));
        } elseif (in_array('ROLE_USER', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('editProfile'));
        }elseif (in_array('ROLE_ARTISTE', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('editProfile'));
        }
    
        throw new \Exception('Aucun rôle trouvé pour cet utilisateur');
    }
    

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
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

        return $isBlocked;
    }
}
