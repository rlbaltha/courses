<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('term')
            ->add('coursename')
            ->add('title')
            ->add('instructorname')
            ->add('callnumber')
            ->add('time')
            ->add('building')
            ->add('room')
            ->add('days')
            ->add('area')
            ->add('may')
            ->add('summerterm')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
