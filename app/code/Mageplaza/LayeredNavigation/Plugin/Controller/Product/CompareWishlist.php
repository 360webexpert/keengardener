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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\Controller\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Wishlist\Controller\Index\Add;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class CompareWishlist
 * @package Mageplaza\LayeredNavigation\Plugin\Controller\Product
 */
class CompareWishlist
{
    /** @var RequestInterface */
    protected $request;

    /** @var Data */
    protected $dataHelper;

    /**
     * Add constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request = $request;
        $this->dataHelper = $helperData;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\Compare\Add|Add $action
     * @param $page
     *
     * @return mixed
     */
    public function afterExecute($action, $page)
    {
        if ($this->dataHelper->isEnabled() && $this->request->isAjax()) {
            return '';
        }

        return $page;
    }
}
