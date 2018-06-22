<?php
/**
 * Created by PhpStorm.
 * User: aleksandar
 * Date: 6/12/18
 * Time: 2:34 PM
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ip', FormTypes\FileType::class, ['label' => 'Ips (csv file)'])
            ->add('convert', FormTypes\SubmitType::class, ['label' => 'Convert', 'attr' => ['class' => 'button primary radius mr1']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class',
           'csrf_protection' => false
        ]);
    }
}