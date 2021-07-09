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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Duplicate;

use Mageplaza\SeoDashboard\Controller\Adminhtml\AbstractExport;

/***
 * Class ExportCsv
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Duplicate
 */
class ExportCsv extends AbstractExport
{
    /***
     * @var string
     */
    protected $extension = 'csv';

    /***
     * @var string
     */
    protected $fileName = 'Duplicate';

    /***
     * @var string
     */
    protected $block = 'Mageplaza\SeoDashboard\Block\Adminhtml\Duplicate\Grid';
}
