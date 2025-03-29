<?php
/*
 * Binôme 20
 * Auguste Celerier, Éric Khella
 */

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

#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(WishlistRepository $wishlistRepo, PurchaseRepository $purchaseRepo): Response
    {
        $topPurchases = $purchaseRepo->findTopExpensivePurchases();
        $topWishlists = $wishlistRepo->findTopWishlistsByTotalValue();

        return $this->render('admin/dashboard.html.twig', [
            'topPurchases' => $topPurchases,
            'topWishlists' => $topWishlists,
        ]);
    }

    #[Route('/users', name: 'users')]
    public function users(UserRepository $userRepo): Response
    {
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
            return $this->redirectToRoute('app_admin_users');
        }

        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('danger', 'You cannot lock yourself.');
            return $this->redirectToRoute('app_admin_users');
        }        

        $user->setIsLocked(!$user->isLocked());
        $entityManager->flush();

        $this->addFlash('success', 'User status updated.');
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/user/{id}/delete', name: 'delete_user')]
    public function deleteUser(int $id, UserRepository $userRepo, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepo->find($id);
        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('danger', 'You cannot delete yourself.');
            return $this->redirectToRoute('app_admin_users');
        }
        
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/user/{id}/toggle-admin', name: 'toggle_admin')]
    public function toggleAdminRole(int $id, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->find($id);
    
        if (!$user) {
            $this->addFlash('danger', 'User not found.');
            return $this->redirectToRoute('app_admin_users');
        }
    
        // Prevent removing own admin rights
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('danger', 'You cannot change your own admin status.');
            return $this->redirectToRoute('app_admin_users');
        }
    
        $roles = $user->getRoles();
    
        if (in_array('ROLE_ADMIN', $roles)) {
            // Remove admin role
            $roles = array_filter($roles, fn($role) => $role !== 'ROLE_ADMIN');
            $this->addFlash('warning', $user->getEmail() . ' is no longer an admin.');
        } else {
            // Add admin role
            $roles[] = 'ROLE_ADMIN';
            $this->addFlash('success', $user->getEmail() . ' has been promoted to ADMIN.');
        }
    
        $user->setRoles(array_values($roles)); // reset roles array keys
        $em->flush();
    
        return $this->redirectToRoute('app_admin_users');
    }
    
}
