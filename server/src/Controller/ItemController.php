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


#[Route('/item')]
final class ItemController extends AbstractController
{
    private WishlistService $wishlistService;

    private ItemService $itemService;
    private EntityManagerInterface $entityManager;

    public function __construct(WishlistService $wishlistService, ItemService $itemService, EntityManagerInterface $entityManager)
    {
        $this->wishlistService = $wishlistService;
        $this->itemService = $itemService;
        $this->entityManager = $entityManager;
    }

    #[Route('/wishlist/{id}', name: 'app_item_list', methods: ['GET'])]
    public function itemsInWishlist(Wishlist $wishlist): Response
    {
        $this->wishlistService->checkOwnerAndInvitedUsers($wishlist);

        return $this->render('item/index.html.twig', [
            'wishlist' => $wishlist,
            'items' => $wishlist->getItems(),
        ]);
    }

    #[Route('/new/{wishlistId}', name: 'app_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $wishlistId): Response
    {
        $wishlist = $this->entityManager->getRepository(Wishlist::class)->find($wishlistId);

        if (!$wishlist) {
            throw $this->createNotFoundException('Wishlist not found');
        }

        $this->wishlistService->checkOwnerAndInvitedUsers($wishlist);

        $item = new Item();
        $item->setWishlist($wishlist);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($item);
            $this->entityManager->flush();

            $this->addFlash('success', 'Item added successfully!');
            return $this->redirectToRoute('app_item_list', ['id' => $wishlist->getId()]);
        }

        return $this->render('item/new.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_item_show', methods: ['GET'])]
    public function show(Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_item_list', ['id' => $item->getWishlist()->getId(), 'wishlist' => $item->getWishlist()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item): Response
    {
        $this->itemService->checkOwnerAndInvitedUsers($item);

        $wishlistId = $item->getWishlist()->getId();

        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }

        $wishlist = $this->entityManager->getRepository(Wishlist::class)->find($wishlistId);

        return $this->redirectToRoute('app_item_list', ['id' => $wishlistId, 'wishlist' => $wishlist], Response::HTTP_SEE_OTHER);
    }
}
