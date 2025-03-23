<?php


namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WishlistController extends AbstractController

{
    #[Route('/wishlist/{id}', name: 'view_wishlist')]
    public function viewWishlist($id, ItemRepository $itemRepository) : Response
    {
       // On récupère les items de la wishlist depuis la base de données
       $items = $itemRepository->findBy(['wishlist' => $id]);

       return $this->render('wishlist/view.html.twig', [
           'wishlist' => [
            'id'=> $id, 
            'title' => 'Wishlist de Rico', 
            'items' => $items
           ]
       ]);
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
