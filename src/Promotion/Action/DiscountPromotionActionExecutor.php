<?php

/*
 * This file is part of catalog promotion plugin for Sylius.
 *
 * (c) Ahmed Kooli
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SnakeTn\CatalogPromotion\Promotion\Action;

use SnakeTn\CatalogPromotion\Promotion\Applicator\ChannelPricingPromotionApplicator;
use SnakeTn\CatalogPromotion\Promotion\Applicator\ChannelPricingPromotionApplicatorInterface;

abstract class DiscountPromotionActionExecutor implements ActionExecutorInterface
{
    /**
     * @var ChannelPricingPromotionApplicatorInterface
     */
    protected $promotionApplicator;

    /**
     * FixedDiscountPromotionActionCommand constructor.
     * @param ChannelPricingPromotionApplicatorInterface $promotionApplicator
     */
    public function __construct(ChannelPricingPromotionApplicatorInterface $promotionApplicator)
    {
        $this->promotionApplicator = $promotionApplicator;
    }

    /**
     * @param array $configuration
     */
    abstract protected function isConfigurationValid(array $configuration);


}
