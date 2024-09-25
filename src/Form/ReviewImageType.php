<?php

namespace App\Form;

use App\Entity\ReviewImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', FileType::class, [
            'label' => 'Téléchargez une image',
            'mapped' => false,  // Important : Ce champ n'est pas directement mappé à la propriété imagePath
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReviewImage::class, // Assurez-vous que ReviewImage est bien la classe de l'entité associée
        ]);
    }
}
