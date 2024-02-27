<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Controller\Admin\Action;

use Setono\SyliusRecommendationsPlugin\Form\Type\EvaluateRecommendationsType;
use Setono\SyliusRecommendationsPlugin\Provider\RecommendationsProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

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

        $productVariants = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $productVariants = $this->recommendationsProvider->getFrequentlyBoughtTogether($form->getData()['productVariant']);
        }

        return new Response($this->twig->render('@SetonoSyliusRecommendationsPlugin/admin/recommendations/index.html.twig', [
            'form' => $form->createView(),
            'productVariants' => $productVariants,
        ]));
    }
}
