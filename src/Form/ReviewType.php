<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{TextType, EmailType, TextareaType, ChoiceType, FileType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\{Email, NotBlank};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Nom du visiteur
            ->add('visitorName', TextType::class, [
                'required' => false,
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Votre nom (facultatif)',
                ],
            ])
            // Email du visiteur
            ->add('visitorEmail', EmailType::class, [
                'required' => false,
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'Votre email (facultatif)',
                ],
                'constraints' => [
                    new Email(['message' => 'Veuillez entrer un email valide.']),
                ],
            ])
            // Note
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            // Commentaire
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre avis.']),
                ],
                'attr' => [
                    'placeholder' => 'Votre avis',
                    'rows' => 5,
                ],
            ])
            // Images
            ->add('images', FileType::class, [
                'label' => 'Vos images',
                'required' => false,
                'multiple' => true,
                'mapped' => false,
                'attr' => [
                    'multiple' => 'multiple',
                    'accept' => 'image/*',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
