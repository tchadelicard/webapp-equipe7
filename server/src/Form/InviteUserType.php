<?php

namespace App\Form;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\UserRepository;

class InviteUserType extends AbstractType
{
    private UserRepository $userRepository;
    private Security $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $users = $this->userRepository->createQueryBuilder('u')
        ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $this->security->getUser()->getId())
            ->getQuery()
            ->getResult();
        $choices = [];
        foreach ($users as $user) {
            $choices[$user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')'] = $user->getId();
        }

        $builder
            ->add('user_ids', ChoiceType::class, [
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Sélectionnez les utilisateurs',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
