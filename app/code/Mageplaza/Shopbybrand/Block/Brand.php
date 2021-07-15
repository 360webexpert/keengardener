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

namespace Mageplaza\Shopbybrand\Block;

use Exception;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use Mageplaza\Shopbybrand\Model\Config\Source\MetaRobots;
use Zend_Db_Select;

/**
 * Class Brand
 *
 * @package Mageplaza\Shopbybrand\Block
 */
class Brand extends Template
{
    /**
     * @var string
     */
    protected $mpRobots;

    /**
     * @type BrandHelper
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $_visibleProduts;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type array
     */
    protected $_char = [];

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var ResourceConnection
     */
    protected $_connection;

    /**
     * Brand constructor.
     *
     * @param Context $context
     * @param Visibility $visibleProduts
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param BrandFactory $brandFactory
     * @param Registry $coreRegistry
     * @param BlockFactory $blockFactory
     * @param AdapterFactory $imageFactory
     * @param BrandHelper $helper
     * @param ResourceConnection $connection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $visibleProduts,
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        Registry $coreRegistry,
        BlockFactory $blockFactory,
        AdapterFactory $imageFactory,
        BrandHelper $helper,
        ResourceConnection $connection,
        array $data = []
    ) {
        $this->_visibleProduts = $visibleProduts;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_brandFactory = $brandFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_blockFactory = $blockFactory;
        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $context->getFilesystem();
        $this->helper = $helper;
        $this->_connection = $connection;

        parent::__construct($context, $data);
    }

    /**
     * @return Template
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $objectManager = ObjectManager::getInstance();
        $category = $objectManager->create(CategoryFactory::class);
        $this->mpRobots = $objectManager->create(MetaRobots::class);
        $action = $this->getRequest()->getFullActionName();

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            if ($action === 'mpbrand_index_index'
                || $action === 'mpbrand_index_view'
                || $action === 'mpbrand_category_view'
            ) {
                $breadcrumbsBlock->addCrumb('home', [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link'  => $this->_storeManager->getStore()->getBaseUrl()
                ]);

                $this->additionCrumb($breadcrumbsBlock);
            }
            if ($action === 'mpbrand_category_view') {
                $catID = $this->getRequest()->getParams();
                if ($category->create()->load($catID)->getData()) {
                    $breadcrumbsBlock->addCrumb('brand', [
                        'label' => __($this->getPageTitle()),
                        'link'  => $this->helper()->getBrandUrl()
                    ])
                        ->addCrumb($category->create()->load($catID)->getUrlKey(), [
                            'label' => $category->create()->load($catID)->getName(),
                            'title' => $category->create()->load($catID)->getName()
                        ]);
                }
                $this->pageConfig->getTitle()->set(
                    $category->create()->load($catID)->getName()
                );

                $this->applySeoCode($category->create()->load($catID));
            } elseif ($action === 'mpbrand_index_view') {
                $breadcrumbsBlock->addCrumb('brand', [
                    'label' => __($this->getPageTitle()),
                    'title' => __($this->getPageTitle()),
                    'link'  => $this->helper()->getBrandUrl()
                ]);
                $this->pageConfig->getTitle()->set($this->getMetaTitle());
            } else {
                $this->pageConfig->getTitle()->set($this->getMetaTitle());
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @param $block
     *
     * @return $this
     */
    protected function additionCrumb($block)
    {
        $title = $this->getPageTitle();
        $block->addCrumb('brand', ['label' => $title]);

        return $this;
    }

    /**
     * @return BrandHelper
     */
    public function helper()
    {
        return $this->helper;
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @param null|string|bool|int|Store $store
     *
     * @return string
     */
    public function getTitleSeparator($store = null)
    {
        $separator = (string) $this->_scopeConfig->getValue(
            'catalog/seo/title_separator',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return ' ' . $separator . ' ';
    }

    /**
     * Get Brand Filter Class for Mixitup
     *
     * @param $brand
     *
     * @return string
     */
    public function getFilterClass($brand)
    {
        return $this->helper()->getFilterClass($brand);
    }

    /**
     * Is show description below Brand name
     *
     * @return mixed
     */
    public function showDescription()
    {
        return $this->helper()->getBrandConfig('show_description');
    }

    /**
     * Is show product quantity near Brand name
     *
     * @return mixed
     */
    public function showProductQty()
    {
        return $this->helper()->getBrandConfig('show_product_qty');
    }

    /**
     * Is show quick view near Brand name
     *
     * @return mixed
     */
    public function showQuickView()
    {
        return $this->helper()->showQuickView();
    }

    /**
     * Get Brand, Category collection function
     *
     * @param null $type
     * @param null $option
     * @param null $char
     *
     * @return Collection
     */
    public function getCollection($type = null, $option = null, $char = null)
    {
        return $this->helper()->getBrandList($type, $option, $char);
    }

    /**
     * Apply Metadata for brand
     *
     * @param null $category
     *
     * @throws LocalizedException
     */
    public function applySeoCode($category = null)
    {
        if ($category) {
            $title = $category->getMetaTitle();
            $this->pageConfig->getTitle()->set($title ?: $category->getName());

            $description = $category->getMetaDescription();
            $this->pageConfig->setDescription($description);

            $keywords = $category->getMetaKeywords();
            $this->pageConfig->setKeywords($keywords);

            $robot = $category->getMetaRobots();
            $array = $this->mpRobots->getOptionArray();
            $this->pageConfig->setRobots($array[$robot]);

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($category->getName());
            }
        }
    }

    /**
     * Get page title
     *
     * @return Phrase
     */
    public function getPageTitle()
    {
        return $this->helper()->getBrandConfig('name') ?: __('Brands');
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        return $this->getPageTitle();
    }

    /**
     * Get quantity of products for brand
     *
     * @param $optionId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductQuantity($optionId)
    {
        $connection = $this->_connection->getConnection();
        $attrId = $this->helper->getAttributeId($this->helper->getAttributeCode());
        $currentStoreId = $this->_storeManager->getStore()->getId();
        $joinTable = $this->helper->versionCompare('2.2.0')
            ? $this->_connection->getTableName('catalog_category_product_index_store' . $currentStoreId)
            : $this->_connection->getTableName('catalog_category_product_index');
        $sql = $connection->select()->distinct()
            ->from(['main' => $this->_connection->getTableName('catalog_product_index_eav')])
            ->join(['extra' => $joinTable], 'main.entity_id = extra.product_id')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['main.entity_id'])
            ->where('main.attribute_id = ' . $attrId)
            ->where('main.value = ' . $optionId)
            ->where('extra.visibility in (2,4)');
        $sql = $this->helper->versionCompare('2.2.0') ? $sql : $sql->where('extra.store_id = ' . $currentStoreId);
        $sql = $connection->select()->from($sql, 'count(*)');
        $result = $connection->fetchAll($sql);

        return array_shift($result[0]);
    }

    /**
     * Get the first character in the brand name
     *
     * @return array
     */
    public function getFirstChar()
    {
        $char = [];
        $collection = $this->checkAction() ? $this->getCollection(
            BrandHelper::CATEGORY,
            $this->getOptionIds()
        ) : $this->getCollection();

        foreach ($collection as $brand => $item) {
            if ($this->helper()->getBrandConfig('brand_filter/encode_key')) {
                $char [] = mb_substr($item['value'], 0, 1, $this->helper()->getBrandConfig('brand_filter/encode_key'));
            } else {
                $char [] = mb_substr($item['value'], 0, 1, 'UTF-8');
            }
        }

        $char = array_unique($char);
        sort($char);

        return $char;
    }

    /**
     * Get brand by alphabet
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAlphaBet()
    {
        $collection = $this->checkAction()
            ? $this->getCollection(BrandHelper::CATEGORY, $this->getOptionIds())
            : $this->getCollection();

        $this->_char = array_unique(
            explode(',', str_replace(' ', '', $this->helper()->getBrandConfig('brand_filter/alpha_bet')))
        );

        /*
         * remove empty  field in array
         */
        foreach ($this->_char as $offset => $row) {
            if (trim($row) === '') {
                unset($this->_char[$offset]);
            }
        }

        /*
         * set default alphabet if leave alphabet config blank
         */
        if (empty($this->_char)) {
            $this->_char = [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                'R',
                'S',
                'T',
                'U',
                'V',
                'W',
                'X',
                'Y',
                'Z'
            ];
        }

        $alphaBet = [];
        $activeChars = [];

        foreach ($collection as $brand) {
            if ($this->getProductQuantity($brand->getOptionId())) {
                if ($encodeKey = $this->helper()->getBrandConfig('brand_filter/encode_key')) {
                    $firstChar = mb_substr($brand->getValue(), 0, 1, $encodeKey);
                } else {
                    $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
                }
                if (!in_array($firstChar, $activeChars, true)) {
                    $activeChars[] = $firstChar;
                }
            }
        }

        $activeChars = $this->helper()->converUppercase($activeChars);

        foreach ($this->_char as $item) {
            $alphaBet[] = [
                'char'   => $item,
                'active' => in_array($item, $activeChars, true)
            ];
        }

        return $alphaBet;
    }

    /**
     * @return array
     */
    public function getOptionIds()
    {
        $catId = $this->getRequest()->getParam('cat_id');
        $result = [];
        $sql = 'main_table.cat_id IN (' . $catId . ')';
        $brands = $this->_categoryFactory->create()->getCategoryCollection($sql, null)->getData();
        foreach ($brands as $brand => $item) {
            $result[] = $item['option_id'];
        }

        return $result;
    }

    /**
     * @param $image
     *
     * @return string
     */
    public function getImageUrl($image)
    {
        return $this->helper->getBrandImageUrl($image);
    }

    /**
     * Resize Image Function
     *
     * @param $image
     * @param null $width
     * @param null $height
     *
     * @return string
     * @throws Exception
     */
    public function resizeImage($image, $width = null, $height = null)
    {
        $absolutePath = $this->getImageUrl($image);

        $imageResized = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('mageplaza/resized/' . $width . '/') . $image->getImage();

        //create image factory...
        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);
        //destination folder
        $destination = $imageResized;
        //save image
        $imageResize->save($destination);

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                      . 'mageplaza/resized/' . $width . '/' . $image->getImage();

        return $resizedURL;
    }

    /**
     * Get category collection
     *
     * @return $this
     */
    public function getCategories()
    {
        return $this->helper()->getCategoryList();
    }

    /**
     * @return bool
     * Check layout
     */
    public function checkAction()
    {
        $action = $this->getRequest()->getFullActionName();

        return $action === 'mpbrand_category_view';
    }

    /**
     * @param $option
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadByOption($option)
    {
        return $this->_brandFactory->create()->loadByOption($option);
    }

    /**
     * @param $catId
     *
     * @return mixed
     */
    public function getBrandQty($catId)
    {
        $sql = 'main_table.cat_id IN (' . $catId . ')';
        $brands = $this->_categoryFactory->create()->getCategoryCollection($sql);

        return $brands->getSize();
    }
}
