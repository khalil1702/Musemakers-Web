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
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\CallbackTransformer;


class OeuvreType extends AbstractType
{

   

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOeuvre', Type\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'oeuvre ne peut pas être vide']),
                ], 'required' => false,
            ])
            ->add('categorieOeuvre' , Type\ChoiceType::class, [
                'choices' => [
                    'Peinture' => 'Peinture',
                    'Sculpture' => 'Sculpture',
                    'Photographie' => 'Photographie',
                ],'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La catégorie de l\'oeuvre ne peut pas être vide']),
                ], 'required' => false,
            ])
            ->add('prixOeuvre', Type\NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix de l\'oeuvre ne peut pas être vide']),
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le prix de l\'oeuvre doit être composé uniquement de chiffres.'
                    ]),
                ], 'required' => false,
            ])
             
            ->add('datecreation', Type\DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de l\'oeuvre ne peut pas être vide']),
                    new LessThanOrEqual([
                        'value' => new \DateTime(),
                        'message' => 'La date de création de l\'oeuvre ne peut pas être dans le futur ',
                    ]),
                ],  
                'required' => false,
            ])
            
            
            ->add('description', Type\TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description de l\'oeuvre ne peut pas être vide']),
                ], 'required' => false,
            ])
          
            ->add('image', FileType::class, [
                'label' => 'Image',
          
                'data' => $options['current_image'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L image de l\'oeuvre ne peut pas être vide']),
                ], 'required' => false,
            ]);

            $builder->get('datecreation')->addModelTransformer(new CallbackTransformer(
                function ($originalDate) {
                    return $originalDate;  // Original value from your entity
                },
                function ($submittedDate) {  // Submitted value from your form
                    return $submittedDate ?: new \DateTime();  // Here we convert null to a DateTime object
                }
            ));
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oeuvre::class,
            'current_image' => null,
        ]);
    }
    
}