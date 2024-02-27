<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class EvaluateRecommendationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productVariant', ProductVariantAutocompleteChoiceType::class, [
            'label' => 'setono_sylius_recommendations.form.evaluate_recommendations.product_variant',
            'required' => true,
        ]);
    }
}
