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

namespace Mageplaza\SeoDashboard\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Mageplaza\SeoDashboard\Helper\Data;

/**
 * Class Duplicate
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Renderer
 */
class Duplicate extends AbstractRenderer
{
    /**
     * Limit
     */
    const LIMIT = 3;

    /**
     * @type ProductRepository
     */
    protected $_productRepository;

    /**
     * @type CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @type PageFactory
     */
    protected $_pageFactory;

    /**
     * @type UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param PageFactory $pageFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PageFactory $pageFactory,
        Context $context,
        array $data = []
    ) {
        $this->_productRepository  = $productRepository;
        $this->_categoryRepository = $categoryRepository;
        $this->_pageFactory        = $pageFactory;
        $this->_urlBuilder         = $context->getUrlBuilder();

        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $ids    = explode(',', $row['entity_ids']);
        $count  = count($ids);
        $result = '<ul>';

        switch ($row['entity']) {
            case Data::CATEGORY_ENTITY:
                for ($i = 0, $iMax = min($count, self::LIMIT); $i < $iMax; $i++) {
                    try {
                        $category = $this->_categoryRepository->get((int) $ids[$i], $row['store']);
                        if ($category && $category->getId()) {
                            $result .= '<li><a class="mp-db-issue" target="_blank" href="'
                                . $this->_urlBuilder->getUrl(
                                    'catalog/category/edit',
                                    ['id' => (int) $ids[$i], 'store' => $row['store']]
                                )
                                . '">'
                                . htmlspecialchars($category->getName())
                                . '</a></li>';
                        }
                    } catch (NoSuchEntityException $e) {
                        $result .= '';
                    }
                }
                break;
            case Data::PAGE_ENTITY:
                for ($i = 0, $iMax = min($count, self::LIMIT); $i < $iMax; $i++) {
                    $page = $this->_pageFactory->create()->load((int) $ids[$i]);
                    if ($page && $page->getId()) {
                        $result .= '<li><a class="mp-db-issue" target="_blank" href="'
                            . $this->_urlBuilder->getUrl('cms/page/edit', ['page_id' => (int) $ids[$i]])
                            . '">'
                            . htmlspecialchars($page->getTitle())
                            . '</a></li>';
                    }
                }
                break;
            default:
                for ($i = 0, $iMax = min($count, self::LIMIT); $i < $iMax; $i++) {
                    try {
                        $product = $this->_productRepository->getById((int) $ids[$i], false, $row['store']);
                        if ($product && $product->getId()) {
                            $result .= '<li><a class="mp-db-issue" target="_blank" href="'
                                . $this->_urlBuilder->getUrl(
                                    'catalog/product/edit',
                                    ['id' => (int) $ids[$i], 'store' => $row['store']]
                                )
                                . '">'
                                . htmlspecialchars($product->getName())
                                . '</a></li>';
                        }
                    } catch (NoSuchEntityException $e) {
                        $result .= '';
                    }
                }
        }
        if ($count > self::LIMIT) {
            $cases  = $count - self::LIMIT;
            $last   = ($cases > 1) ? 'cases' : 'case';
            $result .= '<li>.. <span class="mp-more"><a target="_blank" href="'
                . $this->_urlBuilder->getUrl('seo/duplicate/view', ['issue_id' => $row['issue_id']])
                . '">'
                . '(' . $cases . ' ' . $last . ')</a></span></li>';
        }
        $result .= '</ul>';

        return $result;
    }
}
