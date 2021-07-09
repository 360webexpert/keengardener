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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Category;

use Mageplaza\Shopbybrand\Controller\Adminhtml\Category;

/**
 * Class NewAction
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Category
 */
class NewAction extends Category
{
    /**
     * Forward to edit page
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
