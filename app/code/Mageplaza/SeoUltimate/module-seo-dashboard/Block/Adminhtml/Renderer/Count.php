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

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Mageplaza\SeoDashboard\Helper\Data;

/**
 * Class Count
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Renderer
 */
class Count extends AbstractRenderer
{
    /**
     * Render
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $result = $this->_getValue($row);
        if ($row['entity'] == Data::PAGE_ENTITY) {
            $str = (intval($result) > 1) ? '&nbsp;Words' : '&nbsp;Word';
        } else {
            $str = (intval($result) > 1) ? '&nbsp;Characters' : '&nbsp;Character';
        }

        return $result . $str;
    }
}
