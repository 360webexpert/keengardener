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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Helper\Checklist;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Framework\DataObject;
use Mageplaza\SeoDashboard\Helper\Data as HelperConfig;

/**
 * Class Homepage
 * @package Mageplaza\SeoDashboard\Helper\Checklist
 */
class Homepage
{
    const DEFAULT_HOME_PAGE = 'web/default/cms_home_page';
    const TITLE_PATTEN      = '/<title>(.+)<\/title>/siU';

    /**
     * @var
     */
    protected $_pageCollection;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var HelperConfig
     */
    protected $_config;

    /**
     * @var Content
     */
    protected $_content;

    /**
     * @var array
     */
    protected $metaTag;

    /**
     * @var
     */
    protected $_storeId;

    /**
     * Homepage constructor.
     *
     * @param Content $content
     * @param HelperConfig $config
     * @param Collection $pageCollection
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Content $content,
        HelperConfig $config,
        Collection $pageCollection,
        PageFactory $pageFactory
    ) {
        $this->_content    = $content;
        $this->pageFactory = $pageFactory;
        $this->_config     = $config;
    }

    /**
     * set store id
     *
     * @param $storeId
     */
    public function setStoreId($storeId)
    {
        if (!$this->_storeId) {
            $this->_storeId = $storeId;
        }
    }

    /**
     * Get store id
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * @return $this|DataObject
     */
    public function getPage()
    {
        $pageIdentifier = $this->_config->getConfigValue(self::DEFAULT_HOME_PAGE, $this->getStoreId());
        $pageId         = $this->getPageId($pageIdentifier);
        $pageCollection = $this->pageFactory->create()->getCollection();
        if (is_numeric($pageId)) {
            return $pageCollection->load($pageId);
        } else {
            return
                $pageCollection
                    ->addFieldToFilter('identifier', ['eq' => $pageIdentifier])
                    ->setOrder('page_id', 'ASC')
                    ->getFirstItem();
        }
    }

    /**
     * Has Title
     * @return bool
     */
    public function hasTitle()
    {
        $content = $this->_content->getHtmlContent();
        preg_match(self::TITLE_PATTEN, $content, $matches);
        if (!isset($matches[1])) {
            return false;
        }

        return true;
    }

    /**
     * Has meta description
     * @return bool
     */
    public function hasMetaDescription()
    {
        if (empty($this->getMetaTag()['description'])) {
            return false;
        }

        return true;
    }

    /**
     * Has meta keywords
     * @return bool
     */
    public function hasMetaKeywords()
    {
        if (empty($this->getMetaTag()['keywords'])) {
            return false;
        }

        return true;
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function checkHomepage($storeId)
    {
        $this->setStoreId($storeId);
        if ($this->hasTitle() && $this->hasMetaDescription() && $this->hasMetaKeywords()) {
            return true;
        }

        return false;
    }

    /**
     * Get page id
     *
     * @param $pageIdentifier
     *
     * @return bool
     */
    public function getPageId($pageIdentifier)
    {
        $start = strpos($pageIdentifier, '|');
        if ($start === false) {
            return false;
        }

        return substr($pageIdentifier, $start + 1);
    }

    /**
     * Get meta tag
     * @return mixed
     */
    public function getMetaTag()
    {
        if (!$this->metaTag) {
            $this->metaTag = get_meta_tags($this->_content->getBaseURl($this->getStoreId()));
        }

        return $this->metaTag;
    }
}
