<?php

namespace App\Form;

use App\Entity\Conge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de réponse du responsable à une demande de congé
 * (utilisé aux côtés du workflow : ce form ne fait que capturer
 * le commentaire de réponse, la transition est déclenchée à part).
 */
class CongeReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('decision', ChoiceType::class, [
                'label' => 'Décision',
                'mapped' => false,
                'expanded' => true,
                'choices' => [
                    'Valider' => 'valider',
                    'Refuser' => 'refuser',
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Motif / commentaire de réponse',
                'required' => false,
                'attr' => ['rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conge::class,
        ]);
    }
}
