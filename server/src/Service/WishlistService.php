<?php

namespace App\Service;

use App\Entity\Wishlist;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WishlistService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function checkOwnerAndInvitedUsers(Wishlist $wishlist): void
    {
        $user = $this->security->getUser();

        if ($wishlist->getOwner() === $user) {
            return;
        }

        foreach ($wishlist->getInvitations() as $invitation) {
            if ($invitation->getInvitedUser() === $user) {
                return;
            }
        }

        throw new AccessDeniedException("Access denied: You do not have permission to access this wishlist");
    }

}