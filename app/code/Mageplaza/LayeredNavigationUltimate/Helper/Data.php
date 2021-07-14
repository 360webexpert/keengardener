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

namespace Mageplaza\LayeredNavigationUltimate\Helper;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationPro\Helper\Data as AbstractData;
use Mageplaza\LayeredNavigationPro\Model\Layer\Filter\State;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\SliderType;
use Mageplaza\LayeredNavigationUltimate\Model\ProductsPage;
use Mageplaza\LayeredNavigationUltimate\Model\ProductsPageFactory;

/**
 * Class Data
 * @package Mageplaza\LayeredNavigationPro\Helper
 */
class Data extends AbstractData
{
    const FIELD_SLIDER_TYPE = 'slider_type';
    const FIELD_DISPLAY_TYPE = 'display_type';
    const FIELD_DISPLAY_SIZE = 'display_size';
    const FIELD_DISPLAY_HEIGHT = 'display_height';
    const DEFAULT_ROUTE = 'products';

    /**
     * @var CollectionFactory
     */
    public $attributes;

    /**
     * @var Repository
     */
    public $attributeRepository;

    /**
     * @var ProductsPageFactory
     */
    public $pageFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $attributeCollectionFactory
     * @param Repository $attributeRepository
     * @param ProductsPageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CollectionFactory $attributeCollectionFactory,
        Repository $attributeRepository,
        ProductsPageFactory $pageFactory
    ) {
        $this->attributes = $attributeCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->pageFactory = $pageFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Layer additional field on attribute edit page
     *
     * @return array
     */
    public function getLayerAdditionalFields()
    {
        $fields = parent::getLayerAdditionalFields();

        return array_merge($fields, [
            self::FIELD_DISPLAY_TYPE,
            self::FIELD_DISPLAY_SIZE,
            self::FIELD_DISPLAY_HEIGHT
        ]);
    }

    /**
     * @return bool
     */
    public function enableIonRangeSlider()
    {
        return ($this->getDesignConfig('slider_type') !== '2');
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDesignConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('layered_navigation/design' . $code, $storeId);
    }

    /**
     * Slider skin css file
     *
     * @return string
     */
    public function getSliderSkinFile()
    {
        $sliderType = $this->getDesignConfig('slider_type');
        $fileName = SliderType::getFileName($sliderType);

        return 'Mageplaza_Core/css/skin/' . $fileName;
    }

    /**
     * @param ProductsPage $page
     * @param $position
     *
     * @return bool
     */
    public function canShowProductPageLink($page, $position)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $positionConfig = explode(',', $page->getData('position') ?: '');

        return in_array($position, $positionConfig, true);
    }

    /**
     * @param ProductsPage $page
     *
     * @return string
     */
    public function getProductPageUrl($page)
    {
        return $this->getBaseUrl() . $page->getData('route') . $this->getUrlSuffix();
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * get all attributes code and frontend label
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllAttributes()
    {
        $attributesData = $this->attributes->create()
            ->addIsFilterableFilter()->getData();

        $allAttributes = [];
        if (!empty($attributesData)) {
            foreach ($attributesData as $item) {
                $attributeOptions = $this->getAttributeOptions($item['attribute_code']);
                if (!empty($attributeOptions)) {
                    $allAttributes[$item['attribute_code']] = $item['frontend_label'];
                }
            }
        }

        if (!empty($this->getStateOptions())) {
            $allAttributes['state'] = $this->getModuleConfig('filter/state/label');
        }

        return $allAttributes;
    }

    /**
     * get attribute options by attribute code
     *
     * @param $attCode
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAttributeOptions($attCode)
    {
        $result = [];

        if ($attCode === 'state') {
            $result = $this->getStateOptions();
        } else {
            $options = $this->attributeRepository->get($attCode)->getOptions();
            array_shift($options);

            foreach ($options as $option) {
                if ($option->getValue()) {
                    $result[$attCode . '=' . $option->getValue()] = $option->getLabel();
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getStateOptions()
    {
        $stateConfig = $this->getModuleConfig('filter/state');
        $options = [State::OPTION_NEW, State::OPTION_SALE, State::OPTION_STOCK];
        $itemData = [];
        foreach ($options as $option) {
            if (!$stateConfig[$option . '_enable']) {
                continue;
            }
            $itemData['state=' . $option] = $stateConfig[$option . '_label'];
        }

        return $itemData;
    }

    /**
     * get products page list
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductsPageCollection()
    {
        $pages = $this->pageFactory->create()->getCollection()
            ->addVisibleFilter()
            ->addStoreFilter($this->storeManager->getStore()->getId());

        return $pages;
    }

    /**
     * get products page by id
     *
     * @param $id
     *
     * @return ProductsPage | null
     */
    public function getPageById($id)
    {
        $page = $this->pageFactory->create()->load($id);
        if ($page->getId()) {
            return $page;
        }

        return null;
    }

    /**
     * @param $route
     *
     * @return ProductsPage|null
     * @throws NoSuchEntityException
     */
    public function getPageByRoute($route)
    {
        /** @var ProductsPage $page */
        $page = $this->getProductsPageCollection()
            ->addFieldToFilter('route', $route)
            ->getFirstItem();

        if ($page->getId()) {
            return $page;
        }

        return null;
    }
}
