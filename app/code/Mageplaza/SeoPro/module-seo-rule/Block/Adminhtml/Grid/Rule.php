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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;
use Mageplaza\SeoRule\Model\Rule\Source\Type;

/**
 * Class Rule
 * @package Mageplaza\SeoRule\Block\Adminhtml\Grid
 */
class Rule extends Container
{
    /**
     * @var Type
     */
    protected $ruleType;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param Type $ruleType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Type $ruleType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->ruleType = $ruleType;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id'           => 'add_new_rule_block',
            'label'        => __('Add Rule'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options'      => $this->_getAddRuleButtonOptions(),
        ];

        $this->buttonList->add('add_new_rule_block', $addButtonProps);

        $this->addButton(
            'apply',
            [
                'label'   => __('Apply Rules'),
                'onclick' => 'setLocation(\'' . $this->getApplyRulesUrl() . '\')',
                'class'   => 'apply-rules'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add rule' split button
     * @return array
     */
    protected function _getAddRuleButtonOptions()
    {
        $splitButtonOptions = [];
        $types              = $this->ruleType->toArray();
        foreach ($types as $typeId => $typeLabel) {
            $splitButtonOptions[$typeId] = [
                'label'   => $typeLabel,
                'onclick' => "setLocation('" . $this->_getRuleCreateUrl($typeId) . "')",
                'default' => $this->ruleType->getDefaultType() == $typeId,
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Create url with type
     *
     * @param $type
     *
     * @return string
     */
    protected function _getRuleCreateUrl($type)
    {
        return $this->getUrl('seo/*/new', ['type' => $type]);
    }

    /**
     * Get apply rules url
     * @return string
     */
    protected function getApplyRulesUrl()
    {
        return $this->getUrl('seo/*/applyRules', []);
    }
}
