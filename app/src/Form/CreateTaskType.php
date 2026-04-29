<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('employee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an employee',
                'attr' => ['class' => 'select select-bordered w-full'],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an product',
                'attr' => ['class' => 'select select-bordered w-full'],
            ])
            ->add('destination', EnumType::class, [
                'class' => Location::class,
                'choice_label' => fn($choice) => $choice->value,
                'placeholder' => 'Select the destination',
                'attr' => ['class' => 'select select-bordered w-full'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
