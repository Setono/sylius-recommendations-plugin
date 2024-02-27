<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Controller\Admin\Action;

use Setono\SyliusRecommendationsPlugin\Form\Type\EvaluateRecommendationsType;
use Setono\SyliusRecommendationsPlugin\Provider\RecommendationsProviderInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Webmozart\Assert\Assert;

final class RecommendationsAction
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly RecommendationsProviderInterface $recommendationsProvider,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(EvaluateRecommendationsType::class);
        $form->handleRequest($request);

        $recommendations = [];
        if ($form->isSubmitted() && $form->isValid()) {
            // todo create data class
            $data = $form->getData();
            Assert::isArray($data);
            Assert::keyExists($data, 'productVariant');
            Assert::isInstanceOf($data['productVariant'], ProductVariantInterface::class);

            $recommendations = $this->recommendationsProvider->getFrequentlyBoughtTogether($data['productVariant']);
        }

        return new Response($this->twig->render('@SetonoSyliusRecommendationsPlugin/admin/recommendations/index.html.twig', [
            'form' => $form->createView(),
            'recommendations' => $recommendations,
        ]));
    }
}
