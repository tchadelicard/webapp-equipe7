<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(WishlistRepository $wishlistRepo, PurchaseRepository $purchaseRepo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Access Denied.');
            return $this->redirectToRoute('app_home');
        }

        $topPurchases = $purchaseRepo->findTopExpensivePurchases();
        $topWishlists = $wishlistRepo->findTopWishlistsByValue();

        return $this->render('admin/dashboard.html.twig', [
            'topPurchases' => $topPurchases,
            'topWishlists' => $topWishlists,
        ]);
    }

    #[Route('/users', name: 'users')]
    public function users(UserRepository $userRepo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Access Denied.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/user/{id}/toggle-lock', name: 'toggle_lock')]
    public function toggleLockUser(int $id, UserRepository $userRepo, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepo->find($id);
        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }

        $user->setIsLocked(!$user->isLocked());
        $entityManager->flush();

        $this->addFlash('success', 'User status updated.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/user/{id}/delete', name: 'delete_user')]
    public function deleteUser(int $id, UserRepository $userRepo, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepo->find($id);
        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('admin_users');
    }
}
