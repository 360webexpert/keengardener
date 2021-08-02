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

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class AbstractGiftOption
 * @package Mageplaza\FreeGifts\Block\Gift
 */
abstract class AbstractGiftOption extends Template
{
    /**
     * @var int|string
     */
    protected $_ruleId;

    /**
     * @var HelperRule
     */
    protected $_helperRule;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * AbstractGiftOption constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param HelperRule $helperRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HelperRule $helperRule,
        array $data = []
    ) {
        $this->_helperRule = $helperRule;
        $this->_registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * @param int|string $ruleId
     *
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        $this->_ruleId = $ruleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRuleId()
    {
        return $this->_ruleId;
    }
}
