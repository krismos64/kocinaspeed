<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{TextType, EmailType, TextareaType, ChoiceType, CollectionType, FileType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\{Email, NotBlank};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('visitorName', TextType::class, [
                'required' => false,
                'label' => 'Votre nom',
            ])
            ->add('visitorEmail', EmailType::class, [
                'required' => false,
                'label' => 'Votre email',
            ])
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
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis',
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ReviewImageType::class,  // Assurez-vous d'avoir un formulaire ReviewImageType
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
