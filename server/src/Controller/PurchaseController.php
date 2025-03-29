<?php
/*
 * Binôme 19
 * Julie Descloitres, Jean-Philippe Levesques
 */

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Item;
use App\Form\PurchaseCreateType;
use App\Form\PurchaseUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PurchaseController extends AbstractController
{
    #[Route('/purchase/{itemId}/create', name: 'purchase_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        int $itemId,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/purchases')] string $purchasesDirectory
    ): Response
    {
        $item = $em->getRepository(Item::class)->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

        $user = $this->getUser();

        if ($item->getPurchase()) {
            $buyer = $item->getPurchase()->getBuyer();
            if ($user != $buyer) {
                return $this->redirectToRoute('app_wishlist_public_view', ['uuid' => $item->getWishlist()->getUuid()]);
            }
            $purchase = $item->getPurchase();
            return $this->redirectToRoute('purchase_update', ['itemId' => $itemId]);
        } else{
            $purchase = new Purchase();
        }

        $form = $this->createForm(PurchaseCreateType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $purchaseProofFile = $form->get('purchaseProof')->getData();

            if ($purchaseProofFile) {
                $originalFilename = pathinfo($purchaseProofFile->getClientOriginalName(), PATHINFO_FILENAME);            }
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$purchaseProofFile->guessExtension();                // $filename = uniqid() . '.' . $proofFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $purchaseProofFile->move($purchasesDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $purchase->setProofFilename($newFilename);                


                $purchase->setItem($item);
                $purchase->setBuyer($user);

                $em->persist($purchase);
                $em->flush();

                return $this->redirectToRoute('app_wishlist_public_view', ['uuid' => $item->getWishlist()->getUuid()]);
        }

        return $this->render('purchase/new.html.twig', [
            'item' => $item,
            'form' => $form
        ]);
    }


    #[Route('/purchase/{itemId}/update', name: 'purchase_update', methods: ['GET', 'POST'])]
    public function update(
        Request $request,
        int $itemId,
        EntityManagerInterface $em,
    ): Response
    {
        $item = $em->getRepository(Item::class)->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

        $user = $this->getUser();

        $purchase = $item->getPurchase();

        if ($purchase && $purchase->getBuyer() != $user) {
            throw new AccessDeniedException("Not allowed to edit");
        }

        $form = $this->createForm(PurchaseUpdateType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($purchase);
            $em->flush();
            return $this->redirectToRoute('app_wishlist_public_view', ['uuid' => $item->getWishlist()->getUuid()]);
        }

        return $this->render('purchase/update.html.twig', [
            'item' => $item,
            'form' => $form
        ]);
    }
}