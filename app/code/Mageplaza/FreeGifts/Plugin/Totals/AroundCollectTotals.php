<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Plugin\Totals;

use Exception;
use Magento\Quote\Model\Quote as ModelQuote;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Gift\Item as GiftItem;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class AroundCollectTotals
 * @package Mageplaza\FreeGifts\Plugin
 */
class AroundCollectTotals extends AbstractPlugin
{
    /**
     * @var array
     */
    protected $_addedGifts = [];

    /**
     * @var GiftItem
     */
    protected $_giftItem;

    /**
     * AroundCollectTotals constructor.
     *
     * @param HelperRule $helperRule
     * @param GiftItem $giftItem
     */
    public function __construct(
        HelperRule $helperRule,
        GiftItem $giftItem
    ) {
        $this->_giftItem = $giftItem;

        parent::__construct($helperRule);
    }

    /**
     * @param ModelQuote $subject
     * @param callable $proceed
     *
     * @return ModelQuote
     * @throws Exception
     */
    public function aroundCollectTotals(ModelQuote $subject, callable $proceed)
    {
        $quoteItems = $subject->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            $options = $quoteItem->getProduct()->getTypeInstance()->getOrderOptions($quoteItem->getProduct());
            if (isset($options['info_buyRequest'][HelperRule::OPTION_RULE_ID])) {
                $quoteItem->setData(HelperRule::QUOTE_RULE_ID, $options['info_buyRequest'][HelperRule::OPTION_RULE_ID]);
            }
            if ($itemRuleId = (int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID)) {
                $giftId = (int)$quoteItem->getData('product_id');
                $itemRule = $this->_helperRule->getRuleById($itemRuleId);
                $giftData = $this->_helperRule->setExtraData(false)->processRuleData(
                    $itemRule,
                    true
                )['gifts'];
                $this->_addedGifts[$itemRuleId][$giftId] = $giftId;

                if (isset($giftData[$giftId])
                    && count($this->_addedGifts[$itemRuleId]) <= $itemRule->getMaxGift()) {
                    $quoteItem->setFreeShipping($giftData[$giftId]['free_ship']);
                } else {
                    $this->_giftItem->removeAndDelete($quoteItem);
                    if (isset($this->_addedGifts[$itemRuleId][$giftId])) {
                        unset($this->_addedGifts[$itemRuleId][$giftId]);
                    }
                }
            }
        }

        $subject->getShippingAddress()->unsetData('cached_items_all');
        /** @var ModelQuote $resultCollect */
        $resultCollect = $proceed();
        $resultCollect->save();

        return $resultCollect;
    }
}
