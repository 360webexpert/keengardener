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

namespace Mageplaza\SeoRule\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mageplaza\SeoRule\Model\Rule\Source\Type;

/**
 * Class Edit
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule
 */
class Edit extends Container
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->urlBuilder    = $context->getUrlBuilder();

        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'rule_id';
        $this->_blockGroup = 'Mageplaza_SeoRule';
        $this->_controller = 'adminhtml_rule';

        parent::_construct();

        $rule = $this->_coreRegistry->registry('mageplaza_seorule_rule');

        /**
         * Remove button delete for default rule layer navigation
         */
        if ($rule && $rule->getRuleId() == 1) {
            $this->buttonList->remove('delete');
        }
        $this->buttonList->remove('save');
        if ($this->_backendSession->getSeoRuleType() == Type::LAYERED_NAVIGATION) {
            $this->buttonList->add(
                'save',
                [
                    'label'          => __('Save Rule'),
                    'class'          => 'save primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event'  => 'save',
                                'target' => '#edit_form'
                            ]
                        ]
                    ]
                ]
            );
        } else {
            $this->buttonList->add(
                'save',
                [
                    'label'          => __('Save Rule'),
                    'class'          => 'save primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event'  => 'save',
                                'target' => '#edit_form'
                            ]
                        ]
                    ],
                    'class_name'     => \Magento\Ui\Component\Control\Container::SPLIT_BUTTON,
                    'options'        => [
                        [
                            'label'   => __('Save & Apply'),
                            'class'   => 'save',
                            'onclick' => 'apply(\'saveAndApply\')'
                        ]
                    ]
                ]
            );
        }

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class'          => 'save',
                'label'          => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            20
        );

        $this->_formScripts[] = "
			function apply(option){
				var form = document.getElementById('edit_form');
				form.action = form.action + '?option=' + option
				form.submit();
			}
		";
    }

    /**
     * Get header text
     * @return Phrase
     */
    public function getHeaderText()
    {
        $rule = $this->_coreRegistry->registry('mageplaza_seorule');
        if ($rule->getRuleId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($rule->getName()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Get back url
     * @return string
     */
    public function getBackUrl()
    {
        return $this->urlBuilder->getUrl('*/*/');
    }
}
