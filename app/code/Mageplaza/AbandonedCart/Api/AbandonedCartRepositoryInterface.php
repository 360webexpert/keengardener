<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Api;

/**
 * Interface AbandonedCartRepositoryInterface
 * @package Mageplaza\AbandonedCart\Api
 */
interface AbandonedCartRepositoryInterface
{
    /**
     * @param string $token
     * @param int $quoteId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return boolean
     */
    public function recover($token, $quoteId);
}
