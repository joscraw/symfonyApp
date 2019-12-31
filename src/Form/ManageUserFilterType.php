<?php

namespace App\Form;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class ManageUserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', Filters\TextFilterType::class);
        $builder->add('lastName', Filters\TextFilterType::class);
        $builder->add('email', Filters\TextFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}