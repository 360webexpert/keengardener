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

namespace Mageplaza\FreeGifts\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class GiftInput
 * @package Mageplaza\FreeGifts\Block\Product
 */
class GiftInput extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_FreeGifts::product/gift_input.phtml';

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * GiftInput constructor.
     *
     * @param Context $context
     * @param HelperRule $helperRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperRule $helperRule,
        array $data = []
    ) {
        $this->_helperRule = $helperRule;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $helperData = $this->_helperRule->getHelperData();

        return $helperData->isEnabled() && $helperData->getProductPage();
    }
}
