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

namespace Mageplaza\Shopbybrand\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime as DDateTime;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data as HelperData;

/**
 * Class Sitemap
 * @package Mageplaza\Shopbybrand\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var mixed
     */
    protected $router;

    /**
     * @var BrandFactory
     */
    protected $_brandFactory;

    /**
     * Sitemap constructor.
     *
     * @param HelperData $helper
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param Data $sitemapData
     * @param Filesystem $filesystem
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param PageFactory $cmsFactory
     * @param DateTime $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param DDateTime $dateTime
     * @param BrandFactory $brandFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        HelperData $helper,
        Context $context,
        Registry $registry,
        Escaper $escaper,
        Data $sitemapData,
        Filesystem $filesystem,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        PageFactory $cmsFactory,
        DateTime $modelDate,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        DDateTime $dateTime,
        BrandFactory $brandFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->router = $this->helper->getModuleConfig('general/route');
        $this->_brandFactory = $brandFactory;

        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getBrandsSiteMapCollection($storeId = null)
    {
        $storeId = ($storeId !== null) ? $storeId : 0;
        $brandCollection = $this->_brandFactory->create()->getCollection();
        $brandCollection->addFieldToFilter('store_id', $storeId);
        $brandSiteMapCollection = [];
        if (!$this->router) {
            $this->router = 'brands';
        }
        foreach ($brandCollection as $item) {
            $images = null;
            $imagesCollection = [];
            if ($item->getImage()) {
                $imagesCollection[] = new DataObject(
                    [
                        'url'     => $this->helper->getBrandImageUrl($item),
                        'caption' => null,
                    ]
                );
                $images = new DataObject(['collection' => $imagesCollection]);
            }

            $itemId = $item->getId();
            $brandSiteMapCollection[$itemId] = new DataObject([
                'id'     => $itemId,
                'url'    => $this->router . '/' . $item->getUrlKey() . $this->helper->getUrlSuffix(),
                'images' => $images,
            ]);
        }

        return $brandSiteMapCollection;
    }

    /**
     * init SiteMap items
     */
    public function _initSitemapItems()
    {
        $storeId = $this->getStoreId();
        $this->_sitemapItems[] = new DataObject(
            [
                'collection' => $this->getBrandsSiteMapCollection($storeId),
            ]
        );
        parent::_initSitemapItems();
    }
}
