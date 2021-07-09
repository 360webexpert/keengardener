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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Attribute\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options as OOptions;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\Validator\UniversalFactory;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class Options
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Attribute\Edit
 */
class Options extends OOptions
{
    /**
     * @var Data
     */
    protected $brandHelper;

    /** @var string Option template */
    protected $_template = 'Mageplaza_Shopbybrand::catalog/product/attribute/options.phtml';

    /**
     * Options constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param UniversalFactory $universalFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $attrOptionCollectionFactory,
        UniversalFactory $universalFactory,
        Data $helper,
        array $data = []
    ) {
        $this->brandHelper = $helper;

        parent::__construct($context, $registry, $attrOptionCollectionFactory, $universalFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->brandHelper->versionCompare('2.1.0')) {
            $this->setTemplate('Mageplaza_Shopbybrand::catalog/product/attribute/options_old.phtml');
        }
    }

    /**
     * @return bool
     */
    public function isBrandAttribute()
    {
        return $this->brandHelper->isEnabled() && in_array(
            $this->getAttributeObject()->getAttributeCode(),
            $this->brandHelper->getAllBrandsAttributeCode(),
            true
        );
    }

    /**
     * @return string
     */
    public function getBrandUpdateUrl()
    {
        return $this->getUrl('mpbrand/attribute/update');
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
}
