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

namespace Mageplaza\FreeGifts\Block\Gift;

/**
 * Class ConfigOption
 * @package Mageplaza\FreeGifts\Block\Gift
 */
class ConfigOption extends AbstractGiftOption
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::gift/config_option.phtml';

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return $this->_helperRule->getHelperGift()->getGiftOptions($this->getProduct());
    }

    /**
     * @param mixed $optionId
     *
     * @return string
     */
    public function getSelectName($optionId)
    {
        return 'super_attribute[' . $optionId . ']';
    }
}
