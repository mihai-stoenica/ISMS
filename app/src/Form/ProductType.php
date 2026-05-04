<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product name',
                'attr' => ['class' => 'input input-sm input-bordered w-full']
            ])
            ->add('sellingPrice', MoneyType::class, [
                'label' => 'Selling Price',
                'currency' => false,
                'scale' => 2,
                'divisor' => 1,
                'attr' => [
                    'class' => 'input input-sm input-bordered w-full',
                    'step' => '0.01'
                ],
                'html5' => true,
            ])
            ->add('lowStockThreshold', IntegerType::class, [
                'label' => 'Low Stock Threshold',
                'attr' => ['class' => 'input input-sm input-bordered w-full']
            ])
            ->add('category', EntityType::class, [
                'placeholder' => 'Choose Category',
                'class' => Category::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'select select-sm select-bordered w-full']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
