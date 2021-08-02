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

namespace Mageplaza\FreeGifts\Plugin\QuoteApi;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Api\Data\FreeGiftButtonInterfaceFactory;
use Mageplaza\FreeGifts\Block\Cart\CartRule;
use Mageplaza\FreeGifts\Model\Source\Apply;

/**
 * Class AbstractCart
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
abstract class AbstractCart
{
    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var FreeGiftButtonInterfaceFactory
     */
    protected $freeGiftButton;

    /**
     * @var CartRule
     */
    protected $cartRule;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * GuestCart constructor.
     *
     * @param HelperRule $helperRule
     * @param FreeGiftButtonInterfaceFactory $freeGiftButton
     * @param CartRule $cartRule
     * @param HelperData $helperData
     */
    public function __construct(
        HelperRule $helperRule,
        FreeGiftButtonInterfaceFactory $freeGiftButton,
        CartRule $cartRule,
        HelperData $helperData
    ) {
        $this->_helperRule    = $helperRule;
        $this->freeGiftButton = $freeGiftButton;
        $this->cartRule       = $cartRule;
        $this->helperData     = $helperData;
    }

    /**
     * @param Quote $result
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addFreeGiftRule($result)
    {
        $extAttr = $result->getExtensionAttributes();

        if ($extAttr !== null && $this->_helperRule->getHelperData()->isEnabled()) {
            $rules = $this->_helperRule->setExtraData(false)->setQuote($result)->getAllValidRules();
            $extAttr->setMpFreeGifts($rules);
            $this->cartRule->getValidatedCartRules();
            $freeGiftButton = $this->freeGiftButton->create();
            $freeGiftButton->setIsShowButton(
                ($this->cartRule->hasManualCartRule() || $this->helperData->getCartPage()) && $this->cartRuleId()
            )
                ->setRuleId($this->cartRuleId())
                ->setButtonLabel($this->helperData->getButtonLabel())
                ->setButtonColor($this->helperData->getButtonColor())
                ->setTextColor($this->helperData->getTextColor());

            $extAttr->setMpFreeGiftsButton($freeGiftButton);
        }

        return $result;
    }

    /**
     * @return false|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function cartRuleId()
    {
        $cartRule = $this->_helperRule->setApply(Apply::CART)->getValidatedRules();

        foreach ($cartRule as $rule) {
            return $rule['rule_id'];
        }

        return false;
    }
}
