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

namespace Mageplaza\FreeGifts\Model\Gift;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Item
 * @package Mageplaza\FreeGifts\Model\Gift
 */
class Item extends Quote
{
    /**
     * @param QuoteItem $item
     *
     * @return $this
     */
    public function removeAndDelete(QuoteItem $item)
    {
        if ($item->getId()) {
            $item->setQuote($this);
            $this->setIsMultiShipping(false);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }

            return $this;
        }

        $quoteItems = $this->getItemsCollection();
        $items = [$item];
        if ($item->getHasChildren()) {
            foreach ($item->getChildren() as $child) {
                $items[] = $child;
            }
        }
        foreach ($quoteItems as $key => $quoteItem) {
            foreach ($items as $deleteItem) {
                if ($quoteItem->compare($deleteItem)) {
                    $quoteItems->removeItemByKey($key);
                }
            }
        }

        return $this;
    }
}
