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

use Magento\Checkout\Block\Cart\Additional\Info;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Data as HelperData;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;
use Mageplaza\FreeGifts\Model\Rule as RuleModel;

/**
 * Class AdditionalInfo
 * @package Mageplaza\FreeGifts\Block\Cart
 */
abstract class AdditionalInfo extends Info
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
     * AdditionalInfo constructor.
     *
     * @param Context $context
     * @param HelperRule $helperRule
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperRule $helperRule,
        Registry $registry,
        array $data = []
    ) {
        $this->_helperRule = $helperRule;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return RuleModel|null
     */
    public function getItemRuleId()
    {
        $ruleId = $this->getItem()->getDataByKey(HelperRule::QUOTE_RULE_ID);
        if ($ruleId) {
            return $this->_helperRule->getRuleById($ruleId);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getHelperData()->isEnabled();
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->getItem()->getId();
    }

    /**
     * @return HelperData
     */
    public function getHelperData()
    {
        return $this->_helperRule->getHelperData();
    }
}
