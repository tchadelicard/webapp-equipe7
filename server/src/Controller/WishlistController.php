<?php

namespace App\Controller;

use App\Entity\Wishlist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishlistController extends AbstractController
{
   ###  Affiche la liste des wishlists de l'utilisateur.
    
    #[Route('/wishlists', name: 'wishlist_list', methods: ['GET'])]
    public function list(): Response
    {
        $wishlists = $this->getDoctrine()
                          ->getRepository(Wishlist::class)
                          ->findAll();

        return $this->render('wishlist/list.html.twig', [
            'wishlists' => $wishlists,
        ]);
    }




     ###  Crée une nouvelle wishlist.  
    
    #[Route("/wishlist/new", name:"wishlist_new", methods:["GET", "POST"])]
    public function new(Request $request): Response
    {
        // creation de wishlish
        // créer le formulaire, le gérer et persister l'entité.
        return $this->render('wishlist/new.html.twig');
    }





     ### Modifie une wishlist existante.
    #[Route("/wishlist/{id}/edit", name:"wishlist_edit", methods:["GET", "POST"])]
    public function edit(Request $request, Wishlist $wishlist): Response
    {
        // modifier la wishlist existante
        //  création d'un formulaire pré-rempli avec les données de $wishlist

        return $this->render('wishlist/edit.html.twig', [
            'wishlist' => $wishlist,
        ]);
    }





     ### Supprime une wishlist.
     
    #[Route("/wishlist/{id}/delete", name:"wishlist_delete", methods:["POST"])]
    public function delete(Request $request, Wishlist $wishlist): Response
    {
        // Logique pour supprimer la wishlist
        //++ token CSRF pour la sécurité

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($wishlist);
        $entityManager->flush();

        return $this->redirectToRoute('wishlist_list');
    }

    




     ### Génère l'URL pour le partage privé (pour un utilisateur inscrit).    
    
    #[Route("/wishlist/{id}/share/private", name:"wishlist_share_private", methods:["GET"])]
    public function sharePrivate(Wishlist $wishlist): Response
    {
        // Génération de l'URL privée
        // inclure un token unique pour la sécurité

        $url = $this->generateUrl('wishlist_invitation', ['token' => 'votre_token_unique'], true);

        return $this->render('wishlist/share.html.twig', [
            'url' => $url,
            'type' => 'Privé'
        ]);
    }







    ###  Génère l'URL pour le partage public (pour que n'importe qui puisse voir la wishlist).

    #[Route("/wishlist/{id}/share/public", name:"wishlist_share_public", methods:["GET"])]
    public function sharePublic(Wishlist $wishlist): Response
    {
        // Génération de l'URL publique

        $url = $this->generateUrl('wishlist_public_view', ['id' => $wishlist->getId()], true);

        return $this->render('wishlist/share.html.twig', [
            'url' => $url,
            'type' => 'Public'
        ]);
    }




    #### Affiche la wishlist via un lien public.
    
    #[Route("/wishlist/public/{id}", name:"wishlist_public_view", methods:["GET"])]
    public function publicView(Wishlist $wishlist): Response
    {
        // Afficher la wishlist de manière publique

        return $this->render('wishlist/public_view.html.twig', [
            'wishlist' => $wishlist,
        ]);
    }






    /**
     * Gère l'invitation à une wishlist via un lien contenant un token unique.
     */
    #[Route("/wishlist/invitation/{token}", name:"wishlist_invitation", methods:["GET"])]
    public function invitation(string $token): Response
    {
        // Logique pour retrouver l'invitation en fonction du token
        // Permettre à l'utilisateur d'accepter ou de refuser l'invitation

        return $this->render('wishlist/invitation.html.twig', [
            'token' => $token,
        ]);
    }
}
