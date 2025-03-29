<?php
/*
 * Binôme 21
 * Yiré Soro, Tchadel Icard
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            // Create a new user
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setFirstName("First$i");
            $user->setLastName("Last$i");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            if ($i === 1) {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $user->setIsVerified(true); // Mark user as verified

            $manager->persist($user);

            // Create 5 wishlists for each user
            for ($j = 1; $j <= 5; $j++) {
                $wishlist = new Wishlist();
                $wishlist->setName("Wishlist $j for User $i");
                $wishlist->setDeadline(new \DateTime("+$j months"));
                $wishlist->setOwner($user);

                $manager->persist($wishlist);
            }
        }

        $manager->flush();
    }
}
