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
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class GuestCart
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
class GuestCart extends AbstractCart
{
    /**
     * @param GuestCartRepositoryInterface $subject
     * @param Quote $result
     *
     * @return CartInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @SuppressWarnings("Unused")
     */
    public function afterGet(GuestCartRepositoryInterface $subject, $result)
    {
        return $this->addFreeGiftRule($result);
    }
}
