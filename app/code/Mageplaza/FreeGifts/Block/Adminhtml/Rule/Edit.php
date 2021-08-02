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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\FreeGifts\Model\Rule;

/**
 * Class Edit
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rules
 */
class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Edit constructor.
     *
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->_registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Rule edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mageplaza_FreeGifts';
        $this->_controller = 'adminhtml_rule';
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );

        $ruleId = $this->getRequest()->getParam('rule_id');
        if ($ruleId !== null) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => 'deleteConfirm(\'' . __('Are you sure you want to delete this rule?') . '\', \'' . $this->getUrl(
                        'mpfreegifts/rule/delete',
                        ['rule_id' => $ruleId]
                    ) . '\')'
                ]
            );
        }
    }

    /**
     * Retrieve text for header element depending on loaded rule
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Rule $rule */
        $rule = $this->_registry->registry('current_rule');
        if ($rule->getId()) {
            return __('Edit Rule "%1"', $this->escapeHtml($rule->getDataByKey('name')));
        }

        return __('Create New Rule');
    }
}
