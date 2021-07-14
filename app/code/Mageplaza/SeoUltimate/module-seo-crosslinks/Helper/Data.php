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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Helper;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Seo\Helper\Data as AbstractData;
use Mageplaza\SeoCrosslinks\Model\ResourceModel\Term\Collection;
use Mageplaza\SeoCrosslinks\Model\Term;
use Mageplaza\SeoCrosslinks\Model\Term\Source\Direction;
use Mageplaza\SeoCrosslinks\Model\Term\Source\LinkTarget;
use Mageplaza\SeoCrosslinks\Model\Term\Source\Rel;
use Mageplaza\SeoCrosslinks\Model\Term\Source\TargetType;
use Mageplaza\SeoCrosslinks\Model\TermFactory;

/**
 * Class Data
 * @package Mageplaza\SeoCrosslinks\Helper
 */
class Data extends AbstractData
{
    const TOP   = '_top';
    const SELF  = '_self';
    const BLANK = '_blank';
    /**
     * Cross links path
     */
    const XML_PATH_CROSSLINKS = 'cross_links';

    /**
     * @var TermFactory
     */
    protected $_termFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Category
     */
    protected $_categoryHelper;

    /**
     * @var int
     */
    public $keywordNumber = 0;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TermFactory $termFactory
     * @param ProductFactory $productFactory
     * @param Category $categoryHelper
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TermFactory $termFactory,
        ProductFactory $productFactory,
        Category $categoryHelper,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_termFactory       = $termFactory;
        $this->_productFactory    = $productFactory;
        $this->_categoryHelper    = $categoryHelper;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Get term collection
     *
     * @param int $applyFor
     *
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getTermCollection($applyFor)
    {
        return $this->_termFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', Term::STATUS_ENABLED)
            ->addFieldToFilter('stores', [
                ['finset' => Store::DEFAULT_STORE_ID],
                ['finset' => $this->storeManager->getStore()->getId()]
            ])
            ->addFieldToFilter('apply_for', ['finset' => $applyFor])
            ->setOrder('sort_order', 'ASC');
    }

    /**
     * Render replacement string
     *
     * @param Term $term
     *
     * @return string
     */
    public function renderReplacement($term)
    {
        $result = '';
        $rel    = ($rel = $this->toOptionRelValue($term->getRel())) ? ' rel="' . $rel . '"' : '';

        $result .= '<a'
            . ' alt="' . $term->getLinkTitle() . '"'
            . ' target="' . $this->toOptionTargetValue($term->getLinkTarget()) . '"'
            . $rel
            . ' href="' . $this->getTermReference($term) . '">'
            . $term->getKeyword()
            . '</a>';

        return $result;
    }

    /**
     * Get term reference
     *
     * @param Term $term
     *
     * @return string
     */
    public function getTermReference($term)
    {
        switch ($term->getReference()) {
            case TargetType::CATEGORY:
                try {
                    $category = $this->categoryRepository->get(
                        $term->getRefCategoryId(),
                        $this->storeManager->getStore()->getId()
                    );

                    return $category->getUrl();
                } catch (Exception $e) {
                    return '';
                }
            case TargetType::PRODUCT_SKU:
                $product = $this->_productFactory->create()->getCollection()
                    ->addFieldToFilter('sku', $term->getRefProductSku())
                    ->getFirstItem();

                return ($product) ? $product->getProductUrl() : '';
            default:
                return $this->_urlBuilder->getBaseUrl() . $term->getRefStaticUrl();
        }
    }

    /**
     * To option Rel value
     *
     * @param String $value
     *
     * @return string
     */
    public function toOptionRelValue($value)
    {
        switch ($value) {
            case Rel::DOFOLOW:
                return '';
            default:
                return 'nofollow';
        }
    }

    /**
     * To option Target value
     *
     * @param String $value
     *
     * @return string
     */
    public function toOptionTargetValue($value)
    {
        switch ($value) {
            case LinkTarget::_TOP_FULL_BODY_OF_THE_WINDOW:
                return self::TOP;
            case LinkTarget::_SELF_CURRENT_TAB:
                return self::SELF;
            default:
                return self::BLANK;
        }
    }

    /**
     * Is enable seo cross links
     * @return bool|mixed
     */
    public function isEnableSeoCrossLinks()
    {
        return $this->isEnabled()
            && $this->getConfigValue(self::CONFIG_MODULE_PATH . '/' . self::XML_PATH_CROSSLINKS . '/enable');
    }

    /**
     * Get pattern by key word
     *
     * @param $keyword
     *
     * @return string
     */
    public function getPatternByKeyword($keyword)
    {
        return "/(?!(?:[^<]+>|[^>]+<\/a>))\b($keyword)\b/s";
    }

    /**
     * Replace keyword
     *
     * @param Term $term
     * @param $content
     *
     * @return null|string|string[]
     */
    public function replaceKeyword($term, &$content)
    {
        /**
         * Get all keyword matches by pattern
         */
        preg_match_all($this->getPatternByKeyword(($term->getKeyword())), $content, $matches);
        if (count($matches[0]) == 0) {
            return $content;
        }
        /**
         * Get key of keyword
         */
        if ($term->getDirection() == Direction::TOP_DOWN) {
            $tmpData = array_slice(array_values(array_keys($matches[0])), 0, $term->getLimit());
        } elseif ($term->getDirection() == Direction::BOTTOM_UP) {
            $tmpData = array_slice(array_reverse(array_values(array_keys($matches[0]))), 0, $term->getLimit());
        } else {
            $randomArray = array_values(array_keys($matches[0]));
            shuffle($randomArray);
            $tmpData = array_slice($randomArray, 0, $term->getLimit());
        }

        /**
         * Replace keyword with key data
         */
        $keywordNumber = 0;
        $content       = preg_replace_callback(
            $this->getPatternByKeyword($term->getKeyword()),
            function ($matchesCallback) use ($term, $tmpData, &$keywordNumber) {
                if (in_array($keywordNumber, $tmpData)) {
                    $value = $this->renderReplacement($term);
                } else {
                    $value = $matchesCallback[0];
                }
                $keywordNumber++;

                return $value;
            },
            $content
        );

        return $content;
    }
}
