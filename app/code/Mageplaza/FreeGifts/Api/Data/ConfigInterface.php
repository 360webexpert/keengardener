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
 * Interface ConfigInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface ConfigInterface
{
    /**
     * @return \Mageplaza\FreeGifts\Api\Data\GeneralConfigInterface
     */
    public function getGeneral();

    /**
     * @param \Mageplaza\FreeGifts\Api\Data\GeneralConfigInterface $value
     *
     * @return $this
     */
    public function setGeneral($value);

    /**
     * @return \Mageplaza\FreeGifts\Api\Data\DisplayConfigInterface
     */
    public function getDisplay();

    /**
     * @param \Mageplaza\FreeGifts\Api\Data\DisplayConfigInterface $value
     *
     * @return $this
     */
    public function setDisplay($value);

    /**
     * @return \Mageplaza\FreeGifts\Api\Data\DesignConfigInterface
     */
    public function getDesign();

    /**
     * @param \Mageplaza\FreeGifts\Api\Data\DesignConfigInterface $value
     *
     * @return $this
     */
    public function setDesign($value);
}
