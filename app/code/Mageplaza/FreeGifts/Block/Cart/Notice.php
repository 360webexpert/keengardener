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

namespace Mageplaza\FreeGifts\Block\Cart;

/**
 * Class Notice
 * @package Mageplaza\FreeGifts\Block\Cart
 */
class Notice extends AdditionalInfo
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::cart/notice.phtml';

    /**
     * @return string
     */
    public function getNoticeText()
    {
        if ($rule = $this->getItemRuleId()) {
            return $rule->isAllowNotice() ? $rule->getNoticeContent() : null;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getGiftIconUrl()
    {
        return $this->getHelperData()->getGiftIcon();
    }
}
