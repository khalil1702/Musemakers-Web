<?php

namespace App\Form;

use App\Entity\Oeuvre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class OeuvreType extends AbstractType
{

   

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOeuvre', Type\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'oeuvre ne peut pas être vide']),
                ],
            ])
            ->add('categorieOeuvre' , Type\ChoiceType::class, [
                'choices' => [
                    'Peinture' => 'Peinture',
                    'Sculpture' => 'Sculpture',
                    'Photographie' => 'Photographie',
                ],'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La catégorie de l\'oeuvre ne peut pas être vide']),
                ],
            ])
            ->add('prixOeuvre', Type\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix de l\'oeuvre ne peut pas être vide']),
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le prix de l\'oeuvre doit être composé uniquement de chiffres.'
                    ]),
                ],
            ])
            ->add('datecreation', Type\DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de création de l\'oeuvre ne peut pas être vide']),
                    new Assert\LessThanOrEqual([
                        'value' => new \DateTime(),
                        'message' => 'La date de création de l\'oeuvre ne peut pas être dans le futur',
                    ]),
                ],
            ])
            
            ->add('description', Type\TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description de l\'oeuvre ne peut pas être vide']),
                ],
            ])
          
            ->add('image', FileType::class, [
                'label' => 'Image',
          
                'data' => $options['current_image'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L image de l\'oeuvre ne peut pas être vide']),
                ],
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oeuvre::class,
            'current_image' => null,
        ]);
    }
}
