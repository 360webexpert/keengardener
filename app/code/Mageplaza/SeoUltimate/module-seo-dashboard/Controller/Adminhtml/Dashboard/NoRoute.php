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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard;

use Magento\Backend\Controller\Adminhtml\Dashboard\AjaxBlock;
use Magento\Framework\Controller\Result\Raw;

/**
 * Class NoRoute
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard
 */
class NoRoute extends AjaxBlock
{
    /**
     * Gets all no route uri
     *
     * @return $this|Raw
     */
    public function execute()
    {
        $output    = $this->layoutFactory->create()
            ->createBlock('Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard\Tab\NoRoute')
            ->toHtml();
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($output);
    }
}
