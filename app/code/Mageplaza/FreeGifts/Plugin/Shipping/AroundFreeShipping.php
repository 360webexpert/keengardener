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

namespace Mageplaza\FreeGifts\Plugin\Shipping;

use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Plugin\AbstractPlugin;

/**
 * Class AroundFreeShipping
 * @package Mageplaza\FreeGifts\Plugin
 */
class AroundFreeShipping extends AbstractPlugin
{
    /**
     * @param Calculator $subject
     * @param callable $proceed
     * @param AbstractItem $item
     *
     * @return mixed
     * @SuppressWarnings("Unused")
     */
    public function aroundProcessFreeShipping(Calculator $subject, callable $proceed, AbstractItem $item)
    {
        if (!$this->isEnabled()) {
            return $proceed($item);
        }

        if ((int)$item->getDataByKey(HelperRule::QUOTE_RULE_ID) && $item->getFreeShipping()) {
            $item->setFreeShipping(true);
        }

        return $proceed($item);
    }
}
