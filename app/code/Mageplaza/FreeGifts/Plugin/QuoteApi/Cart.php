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

namespace Mageplaza\FreeGifts\Plugin\QuoteApi;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Cart
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
class Cart extends AbstractCart
{
    /**
     * @param CartManagementInterface $subject
     * @param Quote $result
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings("Unused")
     */
    public function afterGetCartForCustomer(CartManagementInterface $subject, $result)
    {
        return $this->addFreeGiftRule($result);
    }
}
