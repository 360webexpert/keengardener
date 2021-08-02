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

namespace Mageplaza\FreeGifts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AddProductComplete
 * @package Mageplaza\FreeGifts\Observer
 */
class QuoteAddAfter implements ObserverInterface
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * QuoteRemoveItem constructor.
     *
     * @param HelperRule $helperRule
     */
    public function __construct(
        HelperRule $helperRule
    ) {
        $this->_helperRule = $helperRule;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperRule->getHelperData()->isEnabled()) {
            return;
        }

        $quoteItems = $observer->getEvent()->getDataByKey('items');
        /** @var QuoteItem $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $options = $quoteItem->getProduct()->getTypeInstance()->getOrderOptions($quoteItem->getProduct());
            if (isset($options['info_buyRequest'][HelperRule::OPTION_RULE_ID])) {
                $quoteItem->setData(HelperRule::QUOTE_RULE_ID, $options['info_buyRequest'][HelperRule::OPTION_RULE_ID]);
            }
        }
    }
}
