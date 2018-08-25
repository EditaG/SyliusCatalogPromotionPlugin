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

use SnakeTn\CatalogPromotion\Model\ChannelPricing;
use SnakeTn\CatalogPromotion\Entity\PromotionAction;
use SnakeTn\CatalogPromotion\Entity\Promotion;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

class FixedPricePromotionActionExecutor extends DiscountPromotionActionExecutor
{
    /**
     * @param array $configuration
     */
    protected function isConfigurationValid(array $configuration)
    {
        Assert::keyExists($configuration, 'amount');
        Assert::isArray($configuration['amount']);
    }

    /**
     * @param ChannelPricing  $subject
     * @param PromotionAction $action
     *
     * @return bool
     */
    public function execute(ChannelPricing $subject, PromotionAction $action): bool
    {
        if (!isset($action->getConfiguration()[$subject->getChannelCode()])) {
            return false;
        }

        try {
            $this->isConfigurationValid($action->getConfiguration()[$subject->getChannelCode()]);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        $promotionAmount = $this->calculatePromotionAmount(
            $subject->getPromotionSubjectTotal(),
            $action->getConfiguration()[$subject->getChannelCode()]['amount']
        );

        $this->promotionApplicator->apply($subject, $promotionAmount);

        return true;
    }

    /**
     * @param int $promotionSubjectTotal
     * @param int $targetPromotionAmount
     *
     * @return mixed
     */
    private function calculatePromotionAmount(int $promotionSubjectTotal, $targetPromotionAmount)
    {
        return $targetPromotionAmount;
    }
}
