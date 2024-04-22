<?php

namespace App\Form;

use App\Entity\User;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('nomUser', null, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Votre nom ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('prenomUser', null, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Votre prénom ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('numTel', TelType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Votre numéro de téléphone doit contenir uniquement des chiffres',
                    ]),
                ],
            ])
        ->add('numTel', TelType::class)
        ->add('image', FileType::class, [
            'label' => 'Upload Image',
            'mapped' => false,
            'required' => false,
        ])
        ->add('role', ChoiceType::class, [
            'choices'  => [
                'Artiste' => 'ROLE_ARTISTE',
                'Client' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',

            ],
            'expanded' => true,
            'multiple' => false,
        ])
        
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'You should agree to our terms.',
                ]),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('recaptcha', EWZRecaptchaType::class, [
            'mapped' => false,
            'label' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'The captcha is not valid.',
                ]),
                
            ],
        ]);
        
        
        
        ;
    
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
