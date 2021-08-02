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

namespace Mageplaza\FreeGifts\Api\Data;

/**
 * Interface AddGiftDataInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface AddGiftItemInterface
{
    const QUOTE_ID = 'quote_id';
    const RULE_ID = 'rule_id';
    const GIFT_ID = 'gift_id';
    const PRODUCT_OPTION = 'product_option';
    
    /**
     * @return string
     */
    public function getQuoteId();
    
    /**
     * @param string $value
     * @return $this
     */
    public function setQuoteId($value);
    
    /**
     * @return string
     */
    public function getRuleId();
    
    /**
     * @param string $value
     * @return $this
     */
    public function setRuleId($value);
    
    /**
     * @return string
     */
    public function getGiftId();
    
    /**
     * @param string $value
     * @return $this
     */
    public function setGiftId($value);
    
    /**
     * @return \Magento\Quote\Api\Data\ProductOptionInterface|null
     */
    public function getProductOption();
    
    /**
     * @param \Magento\Quote\Api\Data\ProductOptionInterface $value
     * @return $this
     */
    public function setProductOption(\Magento\Quote\Api\Data\ProductOptionInterface $value);
}
