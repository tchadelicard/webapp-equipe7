<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Wishlist;
use App\Form\ItemType;
use App\Service\ItemService;
use App\Service\WishlistService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/items', name: 'app_item_')]
final class ItemController extends AbstractController
{
    private ItemService $itemService;
    private EntityManagerInterface $entityManager;

    public function __construct(ItemService $itemService, EntityManagerInterface $entityManager)
    {
        $this->itemService = $itemService;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_wishlist_items', ['id' => $item->getWishlist()->getId(), 'wishlist' => $item->getWishlist()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        $wishlistId = $item->getWishlist()->getId();

        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }

        $wishlist = $this->entityManager->getRepository(Wishlist::class)->find($wishlistId);

        return $this->redirectToRoute('app_wishlist_items', ['id' => $wishlistId, 'wishlist' => $wishlist], Response::HTTP_SEE_OTHER);
    }
}
