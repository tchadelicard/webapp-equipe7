<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Item;
// use App\Form\PurchaseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/purchase/{itemId}/create', name: 'purchase_create', methods: ['GET', 'POST'])]
    public function create(Request $request, int $itemId, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Item::class)->find($itemId);
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($item->getPurchase()) {
            $buyer = $item->getPurchase()->getBuyer();
            if ($user != $buyer) {
                return $this->redirectToRoute('wishlist_view', ['id' => $item->getWishlist()->getId()]);
            }
            $purchase = $item->getPurchase();
        }
        else{
            $purchase = new Purchase();
        }

        if ($request->isMethod('POST')) {
            $proofFile = $request->files->get('proof');
            if ($proofFile instanceof UploadedFile) {
                $filename = uniqid() . '.' . $proofFile->guessExtension();
                $proofFile->move($this->getParameter('proof_directory'), $filename);
                $purchase->setProofFilename($filename);
            }

            $message = $request->request->get('message');
            $purchase->setCongratulatoryText($message);

            $purchase->setItem($item);
            $purchase->setBuyer($user);

            $em->persist($purchase);
            $em->flush();

            return $this->redirectToRoute('wishlist_view', ['id' => $item->getWishlist()->getId()]);
        }
        return $this->render('wishlist/proof_upload.html.twig', [
            'item' => $item,
            // 'purchase' => $purchase,
            // ici est-ce que c'est bien null si l'item a pas encore ete purchased ?
            'purchase' => $item->getPurchase(),
            'user' => $user
        ]);
    }
}
?>