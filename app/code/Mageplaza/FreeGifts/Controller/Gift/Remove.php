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

namespace Mageplaza\FreeGifts\Controller\Gift;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class Remove
 * @package Mageplaza\FreeGifts\Controller\Gift
 */
class Remove extends AbstractGift
{
    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequestParams();
        $quote = $this->_cart->getQuote();
        $quoteItems = $quote->getAllVisibleItems();

        foreach ($quoteItems as $quoteItem) {
            $itemRuleId = (int)$quoteItem->getDataByKey(HelperRule::QUOTE_RULE_ID);
            $itemId = (int)$quoteItem->getData('product_id');
            if ($data['gift_id'] === $itemId && $data['rule_id'] === $itemRuleId) {
                $this->_cart->removeItem($quoteItem->getItemId());
            }
        }

        try {
            $this->_cart->save();
            $quote->setTotalsCollectedFlag(false)->collectTotals();

            return $this->_json->setData(['success' => true]);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }
}
