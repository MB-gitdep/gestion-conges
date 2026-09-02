<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\UtilisateurService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire d'affectation d'un utilisateur à un service, avec la
 * case "est responsable de ce service".
 */
class UtilisateurServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un service',
            ])
            ->add('estResponsable', CheckboxType::class, [
                'label' => 'Responsable de ce service',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UtilisateurService::class,
        ]);
    }
}
