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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;

/**
 * Class SiteMap
 * @package Mageplaza\SeoDashboard\Helper\Checklist
 */
class SiteMap
{
    /**
     * @var Collection
     */
    protected $_siteMapCollection;

    /**
     * @type DirectoryList
     */
    protected $_directoryList;

    /**
     * SiteMap constructor.
     *
     * @param Collection $siteMapCollection
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Collection $siteMapCollection,
        DirectoryList $directoryList
    ) {
        $this->_siteMapCollection = $siteMapCollection;
        $this->_directoryList     = $directoryList;
    }

    /**
     * Has site map file
     * @return bool
     */
    public function hasSiteMapFile()
    {
        $hasFile                    = false;
        $siteMapCollectionGenerated = $this->_siteMapCollection->addFieldToFilter('sitemap_time', ['neq' => null]);
        foreach ($siteMapCollectionGenerated as $siteMap) {
            if (file_exists($this->_directoryList->getRoot() . $siteMap->getSitemapPath() . $siteMap->getSitemapFilename())) {
                $hasFile = true;
                break;
            }
        }

        return $hasFile;
    }
}
