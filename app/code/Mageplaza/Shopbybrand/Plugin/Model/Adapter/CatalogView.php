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

namespace Mageplaza\Shopbybrand\Plugin\Model\Adapter;

use Magento\Framework\App\RequestInterface;

/**
 * Class Preprocessor
 * @package Mageplaza\LayeredNavigation\Model\Plugin\Adapter
 */
class CatalogView
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * CatalogView constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView $subject
     * @param $result
     *
     * @return bool
     */
    public function afterIsApplicable(
        \Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView $subject,
        $result
    ) {
        if ($this->_request->getFullActionName() === 'mpbrand_index_view') {
            return true;
        }

        return $result;
    }
}
