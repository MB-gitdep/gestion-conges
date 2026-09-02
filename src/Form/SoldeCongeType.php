<?php

namespace App\Form;

use App\Entity\SoldeConge;
use App\Entity\TypeConge;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SoldeCongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => fn (Utilisateur $u) => (string) $u,
                'constraints' => [new NotBlank()],
            ])
            ->add('typeConge', EntityType::class, [
                'class' => TypeConge::class,
                'choice_label' => 'nom',
                'constraints' => [new NotBlank()],
            ])
            ->add('annee', IntegerType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('joursRestants', NumberType::class, [
                'label' => 'Jours restants',
                'scale' => 1,
                'constraints' => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SoldeConge::class,
        ]);
    }
}
