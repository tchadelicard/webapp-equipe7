<?php

namespace App\Service;

use App\Entity\Item;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ItemService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function checkOwnerAndInvitedUsers(Item $item): void
    {
        $user = $this->security->getUser();

        $wishlist = $item->getWishlist();


        if ($wishlist->getOwner() === $user) {
            return;
        }

        foreach ($wishlist->getInvitations() as $invitation) {
            if ($invitation->getInvitedUser() === $user && $invitation->isStatus()) {
                return;
            }
        }

        throw new AccessDeniedException("Access denied: You do not have permission to access this wishlist");
    }

}