<?php


namespace App\Controller;

use App\Entity\Wishlist;
use App\Entity\Invitation;
use App\Form\WishlistType;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wishlists')]
class WishlistController extends AbstractController

{
    /**
     * Liste les wishlists de l'utilisateur connecté
     */
    #[Route('/', name: 'wishlist_list', methods: ['GET'])]
    public function list(WishlistRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        $wishlists = $wishlistRepository->findBy(['owner' => $user]);

        return $this->render('wishlist/index.html.twig', [
            'wishlists' => $wishlists,
            'user' => $user,
        ]);
    }

    /**
     * Créer une nouvelle wishlist
     */
    #[Route('/new', name: 'wishlist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $wishlist = new Wishlist();
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wishlist->setOwner($this->getUser());
            $em->persist($wishlist);
            $em->flush();

            return $this->redirectToRoute('wishlist_list');
        }

        return $this->render('wishlist/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Modifier une wishlist existante
     */
    #[Route('/{id}/edit', name: 'wishlist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlist $wishlist, EntityManagerInterface $em): Response
    {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('wishlist_list');
        }

        return $this->render('wishlist/edit.html.twig', [
            'form' => $form->createView(),
            'wishlist' => $wishlist,
        ]);
    }

    /**
     * Supprimer une wishlist
     */
    #[Route('/{id}/delete', name: 'wishlist_delete', methods: ['POST'])]
    public function delete(Request $request, Wishlist $wishlist, EntityManagerInterface $em): Response
    {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$wishlist->getId(), $request->request->get('_token'))) {
            $em->remove($wishlist);
            $em->flush();
        }

        return $this->redirectToRoute('wishlist_list');
    }

    /**
     * Partager une wishlist (générer une URL publique)
     */
    #[Route('/{id}/share', name: 'wishlist_share', methods: ['GET'])]
    public function share(Wishlist $wishlist, UserRepository $userRepository): Response
    {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $publicUrl = $this->generateUrl('wishlist_public_view', ['id' => $wishlist->getId()], false);

        // Récupère tous les utilisateurs pour le <select> dans share.html.twig
        $users = $userRepository->findAll();

        return $this->render('wishlist/share.html.twig', [
            'wishlist' => $wishlist,
            'public_url' => $publicUrl,
            'users' => $users,  
        ]);
    }

    #[Route('/{id}/share/users', name: 'wishlist_share_to_users', methods: ['POST'])]
    public function shareToUsers(
        Request $request,
        Wishlist $wishlist,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Récupère la liste des IDs utilisateurs depuis le formulaire
        $userIds = $request->request->get('users', []);

        // Trouve les utilisateurs correspondants
        $selectedUsers = $userRepository->findBy(['id' => $userIds]);

        foreach ($selectedUsers as $user) {
            // Vérifie si une invitation n'existe pas déjà pour (user, wishlist)
            $existingInvitation = $em->getRepository(Invitation::class)->findOneBy([
                'invitedUser' => $user,
                'wishlist' => $wishlist,
            ]);

            // Si pas d'invitation existante, on la crée
            if (!$existingInvitation) {
                $invitation = new Invitation();
                $invitation->setInvitedUser($user);
                $invitation->setWishlist($wishlist);
                $invitation->setStatus(false); // Par exemple, false = en attente

                $em->persist($invitation);
            }
        }

        $em->flush();

        $this->addFlash('success', 'La wishlist a été partagée avec les utilisateurs sélectionnés.');

        return $this->redirectToRoute('wishlist_list');
    }

    #[Route('/{id}/share/all', name: 'wishlist_share_to_all', methods: ['POST'])]
    public function shareToAll(
        Wishlist $wishlist,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Récupère tous les utilisateurs
        $allUsers = $userRepository->findAll();

        foreach ($allUsers as $user) {

            if ($user !== $this->getUser()) {
                $existingInvitation = $em->getRepository(Invitation::class)->findOneBy([
                    'invitedUser' => $user,
                    'wishlist' => $wishlist,
                ]);

                if (!$existingInvitation) {
                    $invitation = new Invitation();
                    $invitation->setInvitedUser($user);
                    $invitation->setWishlist($wishlist);
                    $invitation->setStatus(false);

                    $em->persist($invitation);
                }
            }
        }

        $em->flush();

        $this->addFlash('success', 'La wishlist a été partagée avec tous les utilisateurs.');

        return $this->redirectToRoute('wishlist_list');
    }
}
?> 
//     public function viewWishlist() : Response
//     {
//        // Simuler des données pour tester
//        $wishlist = [
//            'id' => 1,
//            'title' => 'Wishlist de Test',
//            'items' => [
//                ['name' => 'Livre Symfony', 'price' => 29.99, 'purchaseUrl' => 'https://exemple.com/livre', 'purchasedBy' => 'Julie', 'description' => 'Ceci un livre enrichissant', 'congratulatoryMessage' => 'Bravo!!'],
//                ['name' => 'Casque Audio', 'price' => 99.99, 'purchaseUrl' => 'https://exemple.com/casque', 'purchasedBy' => '', 'description' => 'Super casque', 'congratulatoryMessage' => '']
//            ]
//        ];
   
//        return $this->render('wishlist/view.html.twig', [
//            'wishlist' => $wishlist
//        ]);
//    }
