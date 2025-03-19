<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WishlistController extends AbstractController
{
    #[Route('/wishlist/123456', name: 'view_wishlist')]
    // public function viewWishlist($id, WishlistItemRepository $wishlistItemRepository)
    // {
    //    // Récupérer les items de la wishlist depuis la base de données
    //    $items = $wishlistItemRepository->findBy(['wishlist' => $id]);

    //    return $this->render('wishlist/view.html.twig', [
    //        'items' => $items
    //    ]);
    //  }
    public function viewWishlist() : Response
    {
       // Simuler des données pour tester
       $wishlist = [
           'id' => 1,
           'title' => 'Wishlist de Test',
           'items' => [
               ['name' => 'Livre Symfony', 'price' => 29.99, 'purchaseUrl' => 'https://exemple.com/livre', 'purchasedBy' => 'Julie', 'description' => '', 'congratulatoryMessage' => 'test'],
               ['name' => 'Casque Audio', 'price' => 99.99, 'purchaseUrl' => 'https://exemple.com/casque', 'purchasedBy' => 'JP', 'description' => 'test', 'congratulatoryMessage' => 'test']
           ]
       ];
   
       return $this->render('wishlist/view.html.twig', [
           'wishlist' => $wishlist
       ]);
   }
}
