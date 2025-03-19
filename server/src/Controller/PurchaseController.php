<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Item;
use App\Form\PurchaseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase/{id}/create', name: 'purchase_create', methods: ['POST'])]
    public function create(Request $request, Item $item, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $purchase = new Purchase();
        $purchase->setItem($item);
        $purchase->setBuyer($user);
        
        // Gestion de l'upload
        $proofFile = $request->files->get('proof');
        if ($proofFile instanceof UploadedFile) {
            $filename = uniqid() . '.' . $proofFile->guessExtension();
            $proofFile->move($this->getParameter('uploads_directory'), $filename);
            $purchase->setProof($filename);
        }
        
        $purchase->setMessage($request->request->get('message'));
        
        $em->persist($purchase);
        $em->flush();

        return $this->redirectToRoute('wishlist_view', ['id' => $item->getWishlist()->getId()]);
    }

    #[Route('/purchase/{id}/update', name: 'purchase_update', methods: ['POST'])]
    public function update(Request $request, Purchase $purchase, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user !== $purchase->getBuyer()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas modifier cette preuve d'achat.");
        }

        // Gestion de la mise à jour de la preuve
        $proofFile = $request->files->get('proof');
        if ($proofFile instanceof UploadedFile) {
            $filename = uniqid() . '.' . $proofFile->guessExtension();
            $proofFile->move($this->getParameter('uploads_directory'), $filename);
            $purchase->setProof($filename);
        }

        $purchase->setMessage($request->request->get('message'));
        
        $em->flush();

        return $this->redirectToRoute('wishlist_view', ['id' => $purchase->getItem()->getWishlist()->getId()]);
    }
}