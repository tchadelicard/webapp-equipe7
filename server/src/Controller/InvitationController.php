<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Repository\InvitationRepository;
use App\Repository\WishlistRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/invitations', name: 'app_invitation_')]
class InvitationController extends AbstractController
{
    /**
     * Liste des invitations pour l'utilisateur connecté
     */
    #[Route(name: 'list', methods: ['GET'])]
    public function listInvitations(
        InvitationRepository $invitationRepository
    ): Response {
        $user = $this->getUser();

        // Récupérer les invitations en attente
        $pendingInvitations = $invitationRepository->findBy([
            'invitedUser' => $user,
            'status' => false,
        ]);

        // Récupérer les invitations acceptées
        $acceptedInvitations = $invitationRepository->findBy([
            'invitedUser' => $user,
            'status' => true,
        ]);

        return $this->render('invitation/list.html.twig', [
            'pending_invitations' => $pendingInvitations,
            'accepted_invitations' => $acceptedInvitations,
        ]);
    }

    /**
     * Accepter une invitation
     */
    #[Route('/{id}/accept/{userId}', name: 'accept', methods: ['GET'])]
    public function acceptInvitation(
        int $id,
        int $userId,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()->getId() !== $userId) {
            return $this->render('invitation/confirmation.html.twig', [
                'success' => false,
                'message' => 'Vous ne pouvez pas accepter une invitation qui ne vous est pas destinée.'
            ]);
        }

        $invitation = $invitationRepository->findOneBy([
            'wishlist' => $id,
            'invitedUser' => $userId,
        ]);

        if (!$invitation) {
            return $this->render('invitation/confirmation.html.twig', [
                'success' => false,
                'message' => 'Invitation introuvable.'
            ]);
        }

        $invitation->setStatus(true);
        $em->flush();

        return $this->render('invitation/confirmation.html.twig', [
            'success' => true,
            'message' => 'Invitation acceptée avec succès.'
        ]);
    }

    /**
     * Refuser une invitation
     */
    #[Route('/{id}/decline/{userId}', name: 'decline', methods: ['GET'])]
    public function declineInvitation(
        int $id,
        int $userId,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()->getId() !== $userId) {
            return $this->render('invitation/confirmation.html.twig', [
                'success' => false,
                'message' => 'Vous ne pouvez pas refuser une invitation qui ne vous est pas destinée.'
            ]);
        }

        $invitation = $invitationRepository->findOneBy([
            'wishlist' => $id,
            'invitedUser' => $userId,
        ]);

        if (!$invitation) {
            return $this->render('invitation/confirmation.html.twig', [
                'success' => false,
                'message' => 'Invitation introuvable.'
            ]);
        }

        $em->remove($invitation);
        $em->flush();

        return $this->render('invitation/confirmation.html.twig', [
            'success' => true,
            'message' => 'Invitation refusée avec succès.'
        ]);
    }
}