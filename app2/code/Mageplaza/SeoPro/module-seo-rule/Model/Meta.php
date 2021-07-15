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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Meta
 * @package Mageplaza\SeoRule\Model
 */
class Meta extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\SeoRule\Model\ResourceModel\Meta');
    }

    /**
     * Apply rule
     *
     * @param $data
     *
     * @return mixed
     */
    public function applyRule($data)
    {
        return $this->getResource()->applyRule($data);
    }

    /**
     * @return mixed
     */
    public function truncateData()
    {
        return $this->getResource()->truncateData();
    }
}
