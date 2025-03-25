<?php


namespace App\Controller;

use App\Entity\Item;
use App\Entity\Wishlist;
use App\Entity\Invitation;
use App\Form\ItemType;
use App\Form\WishlistType;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Service\WishlistService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wishlists', name: 'app_wishlist_')]
class WishlistController extends AbstractController

{
    private WishlistService $wishlistService;
    private WishlistRepository $wishlistRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        WishlistService $wishlistService,
        WishlistRepository $wishlistRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->wishlistService = $wishlistService;
        $this->wishlistRepository = $wishlistRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Liste les wishlists de l'utilisateur connecté
     */
    #[Route(name: 'list', methods: ['GET'])]
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
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $wishlist = new Wishlist();
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wishlist->setOwner($this->getUser());
            $em->persist($wishlist);
            $em->flush();
            return $this->redirectToRoute('app_wishlist_list');
        }

        return $this->render('wishlist/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Modifier une wishlist existante
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlist $wishlist, EntityManagerInterface $em): Response
    {
        $this->wishlistService->checkOwner($wishlist);

        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_wishlist_list');
        }

        return $this->render('wishlist/edit.html.twig', [
            'form' => $form,
            'wishlist' => $wishlist,
        ]);
    }

    /**
     * Supprimer une wishlist
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Wishlist $wishlist, EntityManagerInterface $em): Response
    {
        $this->wishlistService->checkOwner($wishlist);

        if ($this->isCsrfTokenValid('delete'.$wishlist->getId(), $request->request->get('_token'))) {
            $em->remove($wishlist);
            $em->flush();
        }

        return $this->redirectToRoute('app_wishlist_list');
    }

    /**
     * Partager une wishlist (générer une URL publique)
     */
    #[Route('/{id}/share', name: 'share', methods: ['GET'])]
    public function share(Wishlist $wishlist, UserRepository $userRepository): Response
    {
        $this->wishlistService->checkOwner($wishlist);

        $publicUrl = $this->generateUrl('wishlist_public_view', ['id' => $wishlist->getId()], false);

        // Récupère tous les utilisateurs pour le <select> dans share.html.twig
        $users = $userRepository->findAll();

        return $this->render('wishlist/share.html.twig', [
            'wishlist' => $wishlist,
            'public_url' => $publicUrl,
            'users' => $users,  
        ]);
    }

    #[Route('/{id}/share/users', name: 'share_to_users', methods: ['POST'])]
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

        return $this->redirectToRoute('app_wishlist_list');
    }

    #[Route('/{id}/share/all', name: 'share_to_all', methods: ['POST'])]
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

        return $this->redirectToRoute('app_wishlist_list');
    }

    #[Route('/{id}/items', name: 'items', methods: ['GET'])]
    public function getWhishlistItems(Wishlist $wishlist): Response {
        $this->wishlistService->checkOwnerAndInvitedUsers($wishlist);

        return $this->render('wishlist/items.html.twig', [
            'wishlist' => $wishlist
        ]);
    }

    #[Route('/{id}/items/new', name: 'add_item', methods: ['GET', 'POST'])]
    public function addItemToWishlist(Wishlist $wishlist, Request $request): Response
    {
        $this->wishlistService->checkOwnerAndInvitedUsers($wishlist);

        $item = new Item();
        $item->setWishlist($wishlist);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($item);
            $this->entityManager->flush();

            $this->addFlash('success', 'Item added successfully!');
            return $this->redirectToRoute('app_wishlist_items', ['id' => $wishlist->getId(), 'wishlist' => $wishlist]);
        }

        return $this->render('wishlist/newItem.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/public/{uuid}', name: 'public_view')]
    public function publicView(String $uuid): Response
    {
        $wishlist = $this->wishlistRepository->findByUuid($uuid);

        if (!$wishlist) {
            throw $this->createNotFoundException("Wishlist not found");
        }

        return $this->render('wishlist/view.html.twig', [
            'wishlist' => $wishlist
        ]);
    }
}
