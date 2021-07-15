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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Search;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Search\FilterMapper\CustomExclusionStrategy;

/**
 * Class IndexBuilder
 * @package Mageplaza\LayeredNavigationPro\Plugin\CatalogSearch\Model\Search
 */
class IndexBuilder
{
    /**
     * @var CustomExclusionStrategy
     */
    protected $customExclusionStrategy;

    /**
     * IndexBuilder constructor.
     *
     * @param CustomExclusionStrategy $customExclusionStrategy
     */
    public function __construct(
        CustomExclusionStrategy $customExclusionStrategy
    ) {
        $this->customExclusionStrategy = $customExclusionStrategy;
    }

    /**
     * Build index query
     *
     * @param $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundBuild($subject, callable $proceed, RequestInterface $request)
    {
        $select = $proceed($request);
        $filters = $this->getFilters($request->getQuery());
        foreach ($filters as $filter) {
            $this->customExclusionStrategy->apply($filter, $select);
        }

        return $select;
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }
}
