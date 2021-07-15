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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Observer;

use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\LayeredNavigationUltimate\Helper\Data as LayerHelper;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\DisplayType;

/**
 * Class DisplayTypeObserver
 * @package Mageplaza\LayeredNavigationUltimate\Observer
 */
class DisplayTypeObserver implements ObserverInterface
{
    /** @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory */
    protected $_fieldFactory;

    /** @var \Mageplaza\LayeredNavigationUltimate\Model\Config\Source\DisplayType */
    protected $_displayType;

    /**
     * DisplayTypeObserver constructor.
     *
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param \Mageplaza\LayeredNavigationUltimate\Model\Config\Source\DisplayType $displayType
     */
    public function __construct(
        FieldFactory $fieldFactory,
        DisplayType $displayType
    ) {
        $this->_fieldFactory = $fieldFactory;
        $this->_displayType = $displayType;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getEvent()->getData('form');
        $dependencies = $observer->getEvent()->getData('dependencies');
        $fieldset = $form->getElement('layer_fieldset');

        $fieldset->addField(LayerHelper::FIELD_DISPLAY_TYPE, 'select', [
            'name'   => LayerHelper::FIELD_DISPLAY_TYPE,
            'label'  => __("Display Type"),
            'title'  => __("Display Type"),
            'class'  => 'layer_attribute_field',
            'values' => $this->_displayType->getOptionForForm()
        ]);

        $fieldset->addField(LayerHelper::FIELD_DISPLAY_SIZE, 'text', [
            'name'  => LayerHelper::FIELD_DISPLAY_SIZE,
            'label' => __("Number of option"),
            'title' => __("Number of option"),
            'class' => 'layer_attribute_field'
        ]);

        $fieldset->addField(LayerHelper::FIELD_DISPLAY_HEIGHT, 'text', [
            'name'  => LayerHelper::FIELD_DISPLAY_HEIGHT,
            'label' => __("Block height"),
            'title' => __("Block height"),
            'class' => 'layer_attribute_field'
        ]);

        $refField = $this->_fieldFactory->create(['fieldData'   => ['value' => '1,2', 'separator' => ','],
                                                  'fieldPrefix' => ''
        ]);
        $dependencies->addFieldMap(LayerHelper::FIELD_DISPLAY_TYPE, LayerHelper::FIELD_DISPLAY_TYPE)
            ->addFieldMap(LayerHelper::FIELD_DISPLAY_SIZE, LayerHelper::FIELD_DISPLAY_SIZE)
            ->addFieldMap(LayerHelper::FIELD_DISPLAY_HEIGHT, LayerHelper::FIELD_DISPLAY_HEIGHT)
            ->addFieldDependence(LayerHelper::FIELD_DISPLAY_TYPE, 'is_filterable', $refField)
            ->addFieldDependence(LayerHelper::FIELD_DISPLAY_SIZE, 'is_filterable', $refField)
            ->addFieldDependence(LayerHelper::FIELD_DISPLAY_HEIGHT, 'is_filterable', $refField)
            ->addFieldDependence(
                LayerHelper::FIELD_DISPLAY_TYPE,
                LayerHelper::FIELD_FILTER_TYPE,
                LayerHelper::FILTER_TYPE_LIST
            )
            ->addFieldDependence(
                LayerHelper::FIELD_DISPLAY_SIZE,
                LayerHelper::FIELD_FILTER_TYPE,
                LayerHelper::FILTER_TYPE_LIST
            )
            ->addFieldDependence(
                LayerHelper::FIELD_DISPLAY_HEIGHT,
                LayerHelper::FIELD_FILTER_TYPE,
                LayerHelper::FILTER_TYPE_LIST
            )
            ->addFieldDependence(
                LayerHelper::FIELD_DISPLAY_SIZE,
                LayerHelper::FIELD_DISPLAY_TYPE,
                DisplayType::TYPE_HIDDEN
            )
            ->addFieldDependence(
                LayerHelper::FIELD_DISPLAY_HEIGHT,
                LayerHelper::FIELD_DISPLAY_TYPE,
                DisplayType::TYPE_SCROLL
            );

        return $this;
    }
}
