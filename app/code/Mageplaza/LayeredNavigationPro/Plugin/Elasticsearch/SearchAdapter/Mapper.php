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

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\SearchAdapter;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as MagentoMapper5;
use Magento\Elasticsearch\SearchAdapter\Mapper as MagentoMapper;

/**
 * Class Mapper
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\SearchAdapter
 */
class Mapper
{
    /**
     * @param MagentoMapper|MagentoMapper5 $subject
     * @param array $query
     *
     * @return array
     */
    public function afterBuildQuery($subject, array $query)
    {
        $query = $this->fixAggregationSizeLimitedResponse($query);
        return $this->adjustRequestQuery($query);
    }

    /**
     * By default it is not more than 10 options per filter
     *
     * @param array $query
     * @return array
     */
    protected function fixAggregationSizeLimitedResponse(array $query)
    {
        if (isset($query['body']['aggregations']) && is_array($query['body']['aggregations'])) {
            foreach ($query['body']['aggregations'] as &$bucket) {
                if (isset($bucket['terms']) && !isset($bucket['terms']['size'])) {
                    $bucket['terms']['size'] = '1000';
                }
            }
        }

        return $query;
    }

    /**
     * Update a request query. In case it contains values from "MULTIPLY SELECTION" + "AND CONDITION" filter.
     *
     * @param array $query
     * @return array
     */
    private function adjustRequestQuery(array $query)
    {
        if (!isset($query['body']['query']['bool'])) {
            return $query;
        }

        $queryBool = $query['body']['query']['bool'];
        $updatedQueryBool = $this->getQueryWithNodesInRightPlaces($queryBool);
        $query['body']['query']['bool'] = $updatedQueryBool;

        return $query;
    }

    /**
     * @param $queryBool
     * @return array
     */
    private function getQueryWithNodesInRightPlaces($queryBool)
    {
        foreach (['should', 'must'] as $part) {
            if (!isset($queryBool[$part]) || !is_array($queryBool[$part])) {
                continue;
            }

            foreach ($queryBool[$part] as $index => &$node) {
                //there could be either "terms" or "term" unify it
                if (isset($node['terms'])) {
                    $node['term'] = $node['terms'];
                }

                if (!isset($node['term']) || !is_array($node['term'])) {
                    continue;
                }

                //restore unified "term" to "terms"
                if (isset($node['terms'])) {
                    $node['terms'] = $node['term'];
                    unset($node['term']);
                }
            }
        }

        if (isset($queryBool['must'])) {
            //transform [0 => ..., 2 => ..., 5 => ...] to [0 => ..., 1 => ..., 2 => ...]
            $queryBool['must'] = array_values($queryBool['must']);
        }

        if (empty($queryBool['should'])) {
            unset($queryBool['minimum_should_match']);
        }

        return $queryBool;
    }
}
