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
 * Interface DisplayConfigInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface DisplayConfigInterface
{
    /**
     * @return bool
     */
    public function getCartPage();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setCartPage($value);

    /**
     * @return bool
     */
    public function getCartItem();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setCartItem($value);

    /**
     * @return bool
     */
    public function getProductPage();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setProductPage($value);
}
