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

namespace Mageplaza\FreeGifts\Block\Adminhtml;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Mageplaza\FreeGifts\Model\Source\Apply as ApplyType;

/**
 * Class Rule
 * @package Mageplaza\FreeGifts\Block\Adminhtml
 */
class Rule extends Container
{
    /**
     * @var ApplyType
     */
    protected $_applyType;

    /**
     * Rule constructor.
     *
     * @param Context $context
     * @param ApplyType $applyType
     * @param array $data
     */
    public function __construct(
        Context $context,
        ApplyType $applyType,
        array $data = []
    ) {
        $this->_applyType = $applyType;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $buttonData = [
            'id' => 'add_new_rule',
            'label' => __('Add New Rule'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => SplitButton::class,
            'options' => $this->_getButtonDataOptions(),
        ];

        $this->buttonList->add('add_new', $buttonData);

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    protected function _getButtonDataOptions()
    {
        $types = $this->_applyType->getOptionHash();
        $options = [];

        foreach ($types as $type => $label) {
            $options[$type] = [
                'label' => $label,
                'onclick' => "setLocation('" . $this->_getRuleCreateUrl($type) . "')",
                'default' => $type === ApplyType::CART,
            ];
        }

        return $options;
    }

    /**
     * @param $type
     *
     * @return string
     */
    protected function _getRuleCreateUrl($type)
    {
        return $this->getUrl('mpfreegifts/rule/create', ['apply' => $type]);
    }
}
