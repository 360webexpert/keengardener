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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Block\Adminhtml\Term;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\SeoCrosslinks\Model\Term;

/**
 * Class Edit
 * @package Mageplaza\SeoCrosslinks\Block\Adminhtml\Term
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Term edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'term_id';
        $this->_blockGroup = 'Mageplaza_SeoCrosslinks';
        $this->_controller = 'adminhtml_term';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Term'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Term'));
    }

    /**
     * Retrieve text for header element depending on loaded Term
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Term $term */
        $term = $this->_coreRegistry->registry('mageplaza_seocrosslinks_term');
        if ($term->getId()) {
            return __("Edit Term '%1'", $this->escapeHtml($term->getName()));
        }

        return __('New Term');
    }
}
