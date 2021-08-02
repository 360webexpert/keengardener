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
 * Interface FreeGiftButtonInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface FreeGiftButtonInterface
{
    const IS_SHOW_BUTTON = 'is_show_button';
    const RULE_ID        = 'rule_id';
    const BUTTON_LABEL   = 'button_label';
    const BUTTON_COLOR   = 'button_color';
    const TEXT_COLOR     = 'text_color';

    /**
     * @return bool
     */
    public function getIsShowButton();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsShowButton($value);

    /**
     * @return string
     */
    public function getRuleId();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setRuleId($value);

    /**
     * @return string
     */
    public function getButtonLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setButtonLabel($value);

    /**
     * @return string
     */
    public function getButtonColor();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setButtonColor($value);

    /**
     * @return string
     */
    public function getTextColor();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setTextColor($value);
}
