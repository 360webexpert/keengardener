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

namespace Mageplaza\LayeredNavigationPro\Block\Adminhtml\Product\Attribute\Edit\Tab\Renderer;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Table
 * @package Mageplaza\LayeredNavigationPro\Block\Adminhtml\Product\Attribute\Edit\Tab\Renderer
 */
class Table extends AbstractElement
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Table constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param StoreManagerInterface $_storeManager
     * @param Registry $_registry
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        StoreManagerInterface $_storeManager,
        Registry $_registry,
        $data = []
    )
    {
        $this->_storeManager = $_storeManager;
        $this->_registry = $_registry;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $contents = (array)$this->getAttributeObject()->getData('tooltip_content');
        $stores = $this->getStoresSortedBySortOrder();
        $html = '<table class="admin__control-table" id="' . $this->getHtmlId() . '"><thead><tr>';
        foreach ($stores as $_store) {
            $requiredClass = (int)$_store->getId() === Store::DEFAULT_STORE_ID ? '_required' : '';
            $html .= '<th class="col-store-view ' . $requiredClass . '"><span>' . $_store->getName() . '</span></th>';
        }
        $html .= '</tr></thead><tbody><tr>';
        foreach ($stores as $_store) {
            $requiredClass = (int)$_store->getId() === Store::DEFAULT_STORE_ID ? ' required-option' : '';
            $html .= '<td class="col-store-view">
                                <input class="input-text ' . $requiredClass . '"
                                        type="text" name="tooltip_content[' . $_store->getId() . ']"
                                        value="' . $this->_escaper->escapeHtml($this->getTooltipContent($contents,
                    $_store->getId())) . '"
                                        style="width: 100%"/>
                            </td>';
        }
        $html .= '</tr></tbody></table>';

        return $html;
    }

    /**
     * @param array $label
     * @param mixed $storeId
     *
     * @return string
     */
    public function getTooltipContent($label, $storeId)
    {
        if (empty($label) || !isset($label[$storeId])) {
            return '';
        }

        return $label[$storeId];
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasData('stores')) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }

        return $this->_getData('stores');
    }

    /**
     * Returns stores sorted by Sort Order
     *
     * @return array
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() === $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }

                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }

        return $stores;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return AbstractAttribute
     * @codeCoverageIgnore
     */
    private function getAttributeObject()
    {
        return $this->_registry->registry('entity_attribute');
    }
}
