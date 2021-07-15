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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Ui\Component\Product\Form\Categories\Options;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class Category
 * @package Mageplaza\LayeredNavigationPro\Block\Adminhtml\System
 */
class Category extends Field
{
    /**
     * @var CategoryCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var Options
     */
    protected $_option;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Options $options
     * @param CategoryCollectionFactory $collectionFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Options $options,
        CategoryCollectionFactory $collectionFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->_option = $options;
        $this->collectionFactory = $collectionFactory;
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $html = '<div class="admin__field-control admin__control-grouped">';

        $html .= '<div id="layered_navigation_subcategory_slider_categories"  class="admin__field" data-bind="scope:\'category\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input class="validate-select input-text admin__control-text" type="text" name="groups[subcategory_slider][fields][categories][value]" data-bind="value: value" style="display: none;"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= $this->getScriptHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getScriptHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "category": {
                                "component": "uiComponent",
                                "children": {
                                    "select_category": {
                                        "component": "Magento_Catalog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . Data::jsonEncode($this->_option->toOptionArray()) . ',
                                            "value": ' . Data::jsonEncode($this->getValues()) . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "select_category",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';

        return $html;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $values = $this->getConfigData('layered_navigation/subcategory_slider/categories');
        if (!$values) {
            $values = $this->_helperData->getCategory();
        }

        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (empty($values)) {
            return [];
        }

        $options = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIdFilter($values);

        foreach ($collection as $category) {
            $options[] = $category->getId();
        }

        return $options;
    }
}
