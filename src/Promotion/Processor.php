<?php

/*
 * This file is part of catalog promotion plugin for Sylius.
 *
 * (c) Ahmed Kooli
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SnakeTn\CatalogPromotion\Promotion;

use AppBundle\Service\Provider\CacheProvider;
use Doctrine\Common\Collections\Collection;
use SnakeTn\CatalogPromotion\Entity\Promotion;
use SnakeTn\CatalogPromotion\Entity\PromotionRule;
use SnakeTn\CatalogPromotion\Model\ChannelPricing;
use SnakeTn\CatalogPromotion\Promotion\Action\ActionExecutorInterface;
use SnakeTn\CatalogPromotion\Promotion\Checker\Rule\RuleCheckerInterface;
use SnakeTn\CatalogPromotion\Repository\PromotionRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * Class Processor
 *
 */
class Processor
{
    /**
     * @var PromotionRepository
     */
    protected $promotionRepository;

    /**
     * @var array|RuleCheckerInterface
     */
    protected $ruleCheckers = [];
    /**
     * @var array|ActionExecutorInterface
     */
    protected $actionExecutors = [];

    /**
     * @var Promotion[]
     */
    protected $promotions;

    /**
     * @var CacheProvider
     */
    protected $cahce;

    /**
     * Processor constructor.
     *
     * @param PromotionRepository    $promotionRepository
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(PromotionRepository $promotionRepository, CacheProvider $cache)
    {
        $this->promotionRepository = $promotionRepository;
        $this->cache = $cache;
    }

    /**
     * @param ProductVariantInterface $productVariant
     * @param ChannelInterface        $channel
     *
     * @return ChannelPricing
     *
     * @throws \Exception
     */
    public function process(ProductVariantInterface $productVariant, ChannelInterface $channel)
    {
        $originalChannelPricing = $productVariant->getChannelPricingForChannel($channel);

        if (!$originalChannelPricing) {
            $originalChannelPricing = new ChannelPricing();
        }
        $channelPricing = new ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice($originalChannelPricing->getPrice());
        $channelPricing->setOriginalPrice($originalChannelPricing->getOriginalPrice());
        $channelPricing->setBeforeTaxPrice($originalChannelPricing->getBeforeTaxPrice());

        foreach ($this->getPromotions($channel) as $promotion) {
            if ($this->isEligible($productVariant, $promotion)) {
                $this->apply($channelPricing, $promotion);
                $this->setType($channelPricing, $promotion);
            }
        }

        return $channelPricing;
    }

    /**
     * @param string               $ruleCheckerType
     * @param RuleCheckerInterface $ruleChecker
     */
    public function addRuleChecker(string $ruleCheckerType, RuleCheckerInterface $ruleChecker)
    {
        $this->ruleCheckers[$ruleCheckerType] = $ruleChecker;
    }

    /**
     * @param string                  $ruleActionType
     * @param ActionExecutorInterface $actionExecutor
     */
    public function addActionExecutor(string $ruleActionType, ActionExecutorInterface $actionExecutor)
    {
        $this->actionExecutors[$ruleActionType] = $actionExecutor;
    }

    /**
     * @param ProductVariantInterface $productVariant
     * @param Promotion               $promotion
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function isEligible(ProductVariantInterface $productVariant, Promotion $promotion)
    {
        $now = new \DateTime();

        if ($promotion->getStartsAt() < $now && $promotion->getEndsAt() > $now) {
            foreach ($promotion->getRules() as $rule) {
                if (!$this->isEligibleToRule($productVariant, $rule)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param ChannelPricing $channelPricing
     * @param Promotion      $promotion
     *
     * @throws \Exception
     */
    protected function apply(ChannelPricing $channelPricing, Promotion $promotion)
    {
        foreach ($promotion->getActions() as $action) {
            $this->getActionExecutorByActionType($action->getType())->execute($channelPricing, $action);
        }
    }

    /**
     * @param ChannelPricing $channelPricing
     * @param Promotion      $promotion
     */
    protected function setType(ChannelPricing $channelPricing, Promotion $promotion)
    {
        $channelPricing->setPromotionType(substr($promotion->getCode(), strrpos($promotion->getCode(), ' ') + 1));
    }

    /**
     * @param ProductVariantInterface $productVariant
     * @param PromotionRule           $rule
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function isEligibleToRule(ProductVariantInterface $productVariant, PromotionRule $rule)
    {
        /** @var RuleCheckerInterface $checker */
        $checker = $this->getRuleCheckerByRuleType($rule->getType());

        return $checker->isEligible($productVariant, $rule);
    }

    /**
     * @param string $ruleType
     *
     * @return RuleCheckerInterface
     *
     * @throws \Exception
     */
    protected function getRuleCheckerByRuleType(string $ruleType): RuleCheckerInterface
    {
        if (isset($this->ruleCheckers[$ruleType])) {
            return $this->ruleCheckers[$ruleType];
        }
        throw new \Exception(sprintf('rule type %s is not recognized.', $ruleType));
    }

    /**
     * @param string $actionType
     *
     * @return ActionExecutorInterface
     *
     * @throws \Exception
     */
    protected function getActionExecutorByActionType(string $actionType): ActionExecutorInterface
    {
        if (isset($this->actionExecutors[$actionType])) {
            return $this->actionExecutors[$actionType];
        }
        throw new \Exception(sprintf('action type %s is not recognized.', $actionType));
    }

    /**
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function getPromotions(ChannelInterface $channel): array
    {
        if (!$this->promotions) {
            $cacheKey = Promotion::CACHE_TAG_GROUP.'_'.$channel->getCode();

            $callable = [$this->promotionRepository, 'findActiveByChannel'];
            $this->promotions = $this->cache->getItemByKey(
                $cacheKey,
                $callable,
                [$channel],
                43200,
                [Promotion::CACHE_TAG_GROUP]
            );
        }

        return $this->promotions;
    }
}
