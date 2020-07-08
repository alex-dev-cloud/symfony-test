<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', NULL, [
                'constraints' => new Regex([
                    'pattern' => "/^[a-z0-9_-]{3,16}$/",
                    'message' => 'Login is not valid'
                ])
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'The passwords fields must match',
                'required' => true,

                'first_options' => [
                    'label' => 'Password',
                    'constraints' => [
                        new NotBlank(['message' => 'Please, fill this field']),
                        new Length(
                            [
                                'min' => 8,
                                'max' => 16,
                            ]),
                        new Regex([
                            'pattern' => "/^[a-z0-9_-]{8,16}$/",
                            'message' => 'Password is not valid'
                        ])
                    ]
                ],

                'second_options' => [
                    'label' => 'Repeat your password'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
