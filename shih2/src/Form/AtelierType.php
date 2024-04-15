<?php

namespace App\Form;

use App\Entity\Atelier;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class Ateliertype extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('datedebutAtelier', DateType::class, [
            'widget' => 'single_text',
            'constraints' => [
                new NotBlank(['message' => 'La date de début atelier  ne peut pas être vide']),
            ],
            'required' => true,
        ])
        ->add('datefinAtelier', DateType::class, [
            'widget' => 'single_text',
            'constraints' => [
                new NotBlank(['message' => 'La date de fin  atelier ne peut pas être vide']),
                new GreaterThan([
                    'propertyPath' => 'parent.data.datedebutAtelier',
                    'message' => 'La date de fin doit être ultérieure à la date de début.',
                ]),
            ],
            'required' => true,
        ])
        ->add('lienAtelier', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'lienAtelier']
                // Autres contraintes éventuelles pour le champ lienAtelier
            ]);
    }

    // Autres méthodes de configuration si nécessaire





    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Atelier::class,
        ]);
    }
}
