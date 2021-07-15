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
 * @package     Mageplaza_SeoAnalysis
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoAnalysis\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Seo\Helper\Data as HelperData;

/**
 * Class SeoAnalysisProductDataProvider
 * @package Mageplaza\SeoAnalysis\Ui\DataProvider\Product\Modifier
 */
class SeoAnalysisProductDataProvider extends AbstractModifier
{
    const FIELD_PROGRESS_TEMPLATE           = 'Mageplaza_SeoAnalysis/form/element/field-progress';
    const FIELD_INPUT_TEXT_TEMPLATE         = 'Mageplaza_SeoAnalysis/form/element/input';
    const FIELD_META_DATA_PREVIEW_TEMPLATE  = 'Mageplaza_SeoAnalysis/form/element/field-meta-data-preview';
    const FIELD_SEO_INSIGHTS_TEMPLATE       = 'Mageplaza_SeoAnalysis/form/element/field-seo-insights';
    const EXTEND_IMPORT_HANDLER_COMPONENT   = 'Mageplaza_SeoAnalysis/js/view/extend-import-handler';
    const EXTEND_ELEMENT_ABSTRACT_COMPONENT = 'Mageplaza_SeoAnalysis/js/view/extend-element-abstract';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * URL builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SeoAnalysisProductDataProvider constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param ReviewFactory $reviewFactory
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param PriceHelper $priceHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     * @param HelperData $helperData
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ReviewFactory $reviewFactory,
        Registry $registry,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        ScopeConfigInterface $scopeConfig,
        Http $request,
        HelperData $helperData
    ) {
        $this->urlBuilder    = $urlBuilder;
        $this->reviewFactory = $reviewFactory;
        $this->registry      = $registry;
        $this->storeManager  = $storeManager;
        $this->priceHelper   = $priceHelper;
        $this->scopeConfig   = $scopeConfig;
        $this->request       = $request;
        $this->helperData    = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if ($this->isEnableSeoAnalysis()) {
            $productId     = $this->getCurrentProduct()->getId();
            $ratingSummary = $this->getRatingSummary();
            $reviewLabel   = $this->getReviewsCount() > 1 ? __('%1 Reviews', $this->getReviewsCount()) : __(
                '%1 Review',
                $this->getReviewsCount()
            );

            $data[$productId]['product']['seo_analysis'] = [
                'preview_heading' => __('On Search Engine Results Page'),
                'base_url'        => $this->urlBuilder->getBaseUrl(),
                'rating_icon'     => $this->getRatingSummary() . '%',
                'rating'          => __('Rating: ') . round($ratingSummary / 20, 2, PHP_ROUND_HALF_DOWN),
                'review'          => $reviewLabel,
                'salable'         => $this->getIsSalable(),
                'price'           => $this->getProductPriceFormat(),
                'images'          => $this->getProductMediaGallery()
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isEnableSeoAnalysis()) {
            $this->setFieldSearchEngineOptimization(
                $meta,
                'meta_title',
                self::EXTEND_IMPORT_HANDLER_COMPONENT,
                self::FIELD_PROGRESS_TEMPLATE
            );
            $this->setFieldSearchEngineOptimization(
                $meta,
                'meta_description',
                self::EXTEND_IMPORT_HANDLER_COMPONENT,
                self::FIELD_PROGRESS_TEMPLATE
            );
            $this->setFieldSearchEngineOptimization(
                $meta,
                'mp_meta_data_preview',
                self::EXTEND_ELEMENT_ABSTRACT_COMPONENT,
                self::FIELD_META_DATA_PREVIEW_TEMPLATE
            );
            $this->setFieldSearchEngineOptimization(
                $meta,
                'mp_seo_insights',
                self::EXTEND_ELEMENT_ABSTRACT_COMPONENT,
                self::FIELD_SEO_INSIGHTS_TEMPLATE
            );
            $this->setFieldSearchEngineOptimization(
                $meta,
                'url_key',
                self::EXTEND_ELEMENT_ABSTRACT_COMPONENT,
                self::FIELD_INPUT_TEXT_TEMPLATE
            );
            $this->setFieldSearchEngineOptimization(
                $meta,
                'mp_main_keyword',
                self::EXTEND_ELEMENT_ABSTRACT_COMPONENT,
                self::FIELD_INPUT_TEXT_TEMPLATE
            );
            $this->sortFields($meta);
        } else {
            $this->disableFields($meta);
        }

        return $meta;
    }

    /**
     * Set field template in search engine optimization section
     *
     * @param $meta
     * @param $field
     * @param $component
     * @param $template
     *
     * @return string
     */
    public function setFieldSearchEngineOptimization(&$meta, $field, $component, $template)
    {
        if (isset($meta['search-engine-optimization']['children']['container_' . $field])) {
            $meta['search-engine-optimization']['children']['container_' . $field]['children'][$field]['arguments']['data']['config']['elementTmpl'] = $template;
            $meta['search-engine-optimization']['children']['container_' . $field]['children'][$field]['arguments']['data']['config']['component']   = $component;
        }

        return $meta;
    }

    /**
     * Disable fields
     *
     * @param $meta
     */
    public function disableFields(&$meta)
    {
        $fields = ['mp_meta_data_preview', 'mp_seo_insights', 'mp_main_keyword'];
        foreach ($fields as $field) {
            if (isset($meta['search-engine-optimization']['children']['container_' . $field])) {
                $meta['search-engine-optimization']['children']['container_' . $field]['children'][$field]['arguments']['data']['config']['visible'] = 0;
            }
        }
    }

    /**
     * Sort fields in search engine optimization section
     *
     * @param $meta
     *
     * @return mixed
     */
    public function sortFields(&$meta)
    {
        $fields = [
            [
                'attribute'  => 'mp_meta_data_preview',
                'sort_order' => 0,
            ],
            [
                'attribute'  => 'url_key',
                'sort_order' => 1,
            ],
            [
                'attribute'  => 'meta_title',
                'sort_order' => 2,
            ],
            [
                'attribute'  => 'meta_description',
                'sort_order' => 3,
            ],
            [
                'attribute'  => 'meta_keyword',
                'sort_order' => 4,
            ],
            [
                'attribute'  => 'mp_main_keyword',
                'sort_order' => 5
            ],
            [
                'attribute'  => 'mp_seo_insights',
                'sort_order' => 6
            ],
            [
                'attribute'  => 'mp_product_seo_name',
                'sort_order' => 7
            ]
        ];

        foreach ($fields as $field) {
            if (isset($meta['search-engine-optimization']['children']['container_' . $field['attribute']])) {
                $meta['search-engine-optimization']['children']['container_' . $field['attribute']]['arguments']['data']['config']['sortOrder']                                  = $field['sort_order'];
                $meta['search-engine-optimization']['children']['container_' . $field['attribute']]['children'][$field['attribute']]['arguments']['data']['config']['sortOrder'] = $field['sort_order'];
            }
        }

        return $meta;
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get ratings summary
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRatingSummary()
    {
        /** @type Product $product */
        $product = $this->getCurrentProduct();
        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());

        return $this->getCurrentProduct()->getRatingSummary()->getRatingSummary();
    }

    /**
     * Get count of reviews
     *
     * @return int
     */
    public function getReviewsCount()
    {
        return $this->getCurrentProduct()->getRatingSummary()->getReviewsCount();
    }

    /**
     * Get product price after format
     *
     * @return float|string
     */
    public function getProductPriceFormat()
    {
        return $this->priceHelper->currency($this->getCurrentProduct()->getPrice(), true, false);
    }

    /**
     * Get is Salable
     * @return Phrase
     */
    public function getIsSalable()
    {
        return $this->getCurrentProduct()->getIsSalable() ? __('In stock') : __('Out of stock');
    }

    /**
     * Get product media gallery
     * @return array
     */
    public function getProductMediaGallery()
    {
        $imageData = [];
        if ($this->getCurrentProduct()->getMediaGallery('images')) {
            foreach ($this->getCurrentProduct()->getMediaGallery('images') as $image) {
                $imageData[] = $image['label'] ?: '';
            }
        }

        return $imageData;
    }

    /**
     * Is enable seo analysis
     *
     * @return string|null
     */
    protected function isEnableSeoAnalysis()
    {
        return $this->helperData->isEnabled() && $this->helperData->getModuleConfig('page_analysis/enable');
    }
}
