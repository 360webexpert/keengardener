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
 * Interface DesignConfigInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface DesignConfigInterface
{
    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLabel($value);

    /**
     * @return string
     */
    public function getBackgroundColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBackgroundColor($value);

    /**
     * @return string
     */
    public function getTextColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTextColor($value);
}
