<?php
namespace App\Form;

use App\Entity\Exposition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ExpositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', Type\TextType::class, [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom de l\'exposition ne peut pas être vide']),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z\s]*$/',
                    'message' => 'Le nom ne peut contenir que des lettres',
                ]), ],'required' => false,
        ]) 

        ->add('dateDebut', Type\DateType::class, [
            'widget' => 'single_text',
            'constraints' => [
                new Assert\NotBlank(['message' => 'La date de debut de l\'exposition ne peut pas être vide']),
                new Assert\GreaterThanOrEqual([
                    'value' => new \DateTime(), // Vous pouvez remplacer cette valeur par la date de début si nécessaire
                    'message' => 'La date de début doit être supérieure ou égale à la date d\'aujourd\'hui',
                ]),
            ],

            'required' => false,
 ])
 ->add('dateFin', Type\DateType::class, [
    'widget' => 'single_text',
    'constraints' => [
        new Assert\NotBlank(['message' => 'La date de fin de l\'exposition ne peut pas être vide']),
      
    ],
    'required' => false,
])

            ->add('description', Type\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le desc de l\'exposition ne peut pas être vide']),
                ],
                'required' => false,
            ])

            ->add('theme', Type\ChoiceType::class, [
                'placeholder' => 'cliquer pour choisir un thème',
                'choices'  => [
                    'Peinture à l\'huile' => 'Peinture à l\'huile',
                    'Photographie contemporaine' => 'Photographie contemporaine',
                    'Sculptures abstraites' => 'Sculptures abstraites',
                    'Art numérique' => 'Art numérique',
                    'Art moderne' => 'Art moderne',
                    'Street Art' => 'Street Art',
                    'Portraits contemporains' => 'Portraits contemporains',
                    'Art fantastique' => 'Art fantastique',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le thème de l\'exposition ne peut pas être vide']),
                ],
                'required' => false,
                'attr' => [
                    'style' => 'color: black;',
                ],
            ])

            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false, // make it optional
                'data_class' => null, // This is important, it prevents Symfony from trying to convert the uploaded file into an Exposition object
            ])


            
            ->add('heureDebut', Type\TimeType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de début ne peut pas être vide']),
                ],
                'required' => false,
                'widget' => 'single_text', // Render as a single input field
                'attr' => [
                    'class' => 'clockpicker', // Add clockpicker class for custom styling
                ],
            ])
            ->add('heureFin', Type\TimeType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de fin ne peut pas être vide']),
                ],
                'required' => false,
                'widget' => 'single_text', // Render as a single input field
                'attr' => [
                    'class' => 'clockpicker', // Add clockpicker class for custom styling
                ],
            ]);

       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exposition::class,
            'constraints' => [
                new Assert\Callback([$this, 'validate']),
            ],
        ]);
    }

    public function validate(Exposition $exposition, ExecutionContextInterface $context): void
    {
        if ($exposition->getDateDebut() > $exposition->getDateFin()) {
            $context->buildViolation('La date de fin doit être supérieure ou égale à la date de début')
                ->atPath('dateFin')
                ->addViolation();
        }
    }

    public function addEventListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $exposition = $event->getData();
            $form = $event->getForm();

            // Check if the exposition is new (add form) or existing (edit form)
            if (!$exposition || null === $exposition->getId()) {
                // Exposition is new, set image field as required
                $form->add('image', FileType::class, [
                    'label' => 'Image',
                    'constraints' => [
                        new NotBlank(['message' => 'Please upload an image.']),
                    ],
                ]);
            } else {
                // Exposition is existing, set image field as not required
                $form->add('image', FileType::class, [
                    'label' => 'Image',
                    'required' => false, // Make it optional
                ]);
            }
        });


}}


