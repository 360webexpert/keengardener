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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Gift\Item as GiftItem;

/**
 * Class CheckoutCart
 * @package Mageplaza\FreeGifts\Block\Cart
 */
abstract class CheckoutCart extends Template
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var GiftItem
     */
    protected $_giftItem;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * CheckoutCart constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param HelperRule $helperRule
     * @param Registry $registry
     * @param GiftItem $giftItem
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        HelperRule $helperRule,
        Registry $registry,
        GiftItem $giftItem,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_helperRule = $helperRule;
        $this->_registry = $registry;
        $this->_giftItem = $giftItem;

        parent::__construct($context, $data);
    }

    /**
     * @return HelperData
     */
    public function getHelperData()
    {
        return $this->_helperRule->getHelperData();
    }
}
