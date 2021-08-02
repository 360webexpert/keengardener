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
 * Interface GeneralConfigInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface GeneralConfigInterface
{
    /**
     * @return string
     */
    public function getGiftLayout();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGiftLayout($value);

    /**
     * @return bool
     */
    public function getAllowNotice();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setAllowNotice($value);

    /**
     * @return string
     */
    public function getNotice();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNotice($value);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIcon($value);
}
