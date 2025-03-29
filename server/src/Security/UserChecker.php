<?php
/*
 * Binôme 21
 * Yiré Soro, Tchadel Icard
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isLocked()) {
            throw new CustomUserMessageAccountStatusException('Your account is locked .');
        }
    }
}