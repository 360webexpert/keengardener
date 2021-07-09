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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class CartRules
 * @package Mageplaza\AbandonedCart\Model\Config\Source
 */
class CartRules implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $ruleFac;

    /**
     * @param CollectionFactory $ruleFac
     */
    public function __construct(CollectionFactory $ruleFac)
    {
        $this->ruleFac = $ruleFac;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $option         = [['value' => '', 'label' => __('-- Please Select --')]];
        $ruleCollection = $this->ruleFac->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('coupon_type', 2)
            ->addFieldToFilter('use_auto_generation', 1);
        foreach ($ruleCollection->getData() as $rule) {
            $option[] = [
                'value' => $rule['rule_id'],
                'label' => $rule['name']
            ];
        }

        return $option;
    }
}
