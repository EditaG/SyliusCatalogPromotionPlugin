<?php

/*
 * This file is part of catalog promotion plugin for Sylius.
 *
 * (c) Ahmed Kooli
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SnakeTn\CatalogPromotion\Promotion\Applicator;

use SnakeTn\CatalogPromotion\Model\ChannelPricing;

/**
 * Class ChannelPricingPromotionApplicator
 *
 */
class ChannelPricingFixedAmountPromotionApplicator implements ChannelPricingPromotionApplicatorInterface
{
    /**
     * @param ChannelPricing $channelPricing
     * @param                $promotionAmount
     */
    public function apply(ChannelPricing $channelPricing, $promotionAmount): void
    {
        $channelPricing->setPromotionAmount(0);
        $channelPricing->setPrice($promotionAmount['price']);
        $channelPricing->setBeforeTaxPrice($promotionAmount['price_before_tax']);
    }
}
