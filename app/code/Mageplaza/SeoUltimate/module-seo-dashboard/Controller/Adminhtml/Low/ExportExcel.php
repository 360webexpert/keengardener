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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Low;

use Mageplaza\SeoDashboard\Controller\Adminhtml\AbstractExport;

/***
 * Class ExportExcel
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Low
 */
class ExportExcel extends AbstractExport
{
    /***
     * @var string
     */
    protected $extension = 'xml';

    /***
     * @var string
     */
    protected $fileName = 'Low_Word';

    /***
     * @var string
     */
    protected $block = 'Mageplaza\SeoDashboard\Block\Adminhtml\Low\Grid';
}
