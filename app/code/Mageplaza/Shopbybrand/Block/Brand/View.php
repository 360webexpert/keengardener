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

namespace Mageplaza\Shopbybrand\Block\Brand;

use Magento\Cms\Block\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class View
 * @package Mageplaza\Shopbybrand\Block
 */
class View extends Brand
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $brand = $this->getBrand();
        $title = $brand->getPageTitle() ?: $brand->getValue();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('view', ['label' => $title]);
        }

        $description = $brand->getMetaDescription();
        if ($description) {
            $this->pageConfig->setDescription($description);
        }
        $keywords = $brand->getMetaKeywords();
        if ($keywords) {
            $this->pageConfig->setKeywords($keywords);
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        return $this;
    }

    /**
     * @param $block
     *
     * @return $this|Brand
     * @throws NoSuchEntityException
     */
    protected function additionCrumb($block)
    {
        $title = $this->getPageTitle();
        $block->addCrumb('brand', [
            'label' => __($title),
            'title' => __($title),
            'link'  => $this->helper->getBrandUrl()
        ]);

        $brand = $this->getBrand();
        $brandTitle = $brand->getPageTitle() ?: $brand->getValue();
        $block->addCrumb('view', ['label' => $brandTitle]);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->helper()->getBrand();
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        $brand = $this->getBrand();

        $metaTitle = $brand->getMetaTitle();
        if ($metaTitle) {
            return $metaTitle;
        }

        $title = $brand->getPageTitle() ?: $brand->getValue();

        return join($this->getTitleSeparator(), [$title, $this->getPageTitle()]);
    }

    /**
     * @return string
     */
    public function getBrandImage()
    {
        if (!$this->helper->getBrandDetailConfig('show_image')) {
            return '';
        }

        $brand = $this->getBrand();

        return $brand->getImage();
    }

    /**
     * @return string
     */
    public function getBrandDescription()
    {
        if (!$this->helper->getBrandDetailConfig('show_description')) {
            return '';
        }
        $brand = $this->getBrand();

        return $this->helper()->getBrandDescription($brand);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getStaticContent()
    {
        if (!$this->helper->getBrandDetailConfig('show_block')) {
            return '';
        }

        $block = $this->getBrand()->getStaticBlock() ?: $this->helper()->getBrandDetailConfig('default_block');

        $cmsBlock = $this->_blockFactory->create();
        $cmsBlock->load($block, 'identifier');

        $html = '';
        if ($cmsBlock && $cmsBlock->getId()) {
            $html = $this->getLayout()->createBlock(Block::class)
                ->setBlockId($cmsBlock->getId())
                ->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml();
    }
}
