<?php

namespace SnakeTn\CatalogPromotion\Promotion\Applicator;

use SnakeTn\CatalogPromotion\Model\ChannelPricing;

/**
 * Interface ChannelPricingPromotionApplicatorInterface
 *
 */
interface ChannelPricingPromotionApplicatorInterface
{
    /**
     * @param ChannelPricing $channelPricing
     * @param                $promotionAmount
     */
    public function apply(ChannelPricing $channelPricing, $promotionAmount): void;
}
