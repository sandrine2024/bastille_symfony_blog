<?php
namespace App\Security;


use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) 
        {
            return;
        }

    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) 
        {
            return;
        }

        // Le compte utilisateur est expiré, l'utilisateur peut être averti
        if ( ! $user->isVerified()) {
            // Si la méthode isVerified() renvoie false, c'est-à-dire que l'utilisateur n'est pas vérifié,
            throw new CustomUserMessageAccountStatusException('Veuillez vérifier votre compte par email avant de vous connecter.');
            // alors une exception personnalisée est lancée avec un message demandant à l'utilisateur de vérifier son compte par email avant de se connecter.
        }
        
    }
}