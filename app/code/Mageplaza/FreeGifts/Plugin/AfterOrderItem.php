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

namespace Mageplaza\FreeGifts\Plugin;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AfterOrderItem
 * @package Mageplaza\FreeGifts\Plugin
 */
class AfterOrderItem extends AbstractPlugin
{
    /**
     * @param ToOrderItem $subject
     * @param OrderItemInterface $result
     * @param Item|AddressItem $item
     *
     * @return mixed
     * @SuppressWarnings("Unused")
     */
    public function afterConvert(ToOrderItem $subject, OrderItemInterface $result, $item)
    {
        $ruleId = (int)$item->getDataByKey(HelperRule::QUOTE_RULE_ID);
        if ($ruleId === 0 || !$this->isEnabled()) {
            return $result;
        }
        $result->setMpfreegiftsRuleId($ruleId);

        return $result;
    }
}
