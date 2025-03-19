<?php

namespace App\Controller;

use App\Entity\Wishlist;
use App\Form\WishlistType;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
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
            'form' => $form->createView(),
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
    public function share(Wishlist $wishlist): Response
    {
        if ($wishlist->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $publicUrl = $this->generateUrl('wishlist_public_view', ['id' => $wishlist->getId()], 0);

        return $this->render('wishlist/share.html.twig', [
            'wishlist' => $wishlist,
            'public_url' => $publicUrl,
        ]);
    }

    /**
     *  JSP TROP ==Afficher une wishlist publique pour que les gens puissent offrir des cadeaux
     
    *#[Route('/{id}/public', name: 'wishlist_public_view', methods: ['GET'])]
    *public function publicView(Wishlist $wishlist): Response
    *{
    *    return $this->render('wishlist/public.html.twig', [
    *        'wishlist' => $wishlist,
    *    ]);
    *}
        */
}
