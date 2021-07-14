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
 * @package     Mageplaza_SeoUltimate
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

use Magento\Framework\Component\ComponentRegistrar;

$seoMapUltimate = [
    'Mageplaza_SeoAnalysis'   => __DIR__ . '/module-seo-analysis',
    'Mageplaza_SeoCrosslinks' => __DIR__ . '/module-seo-crosslinks',
    'Mageplaza_SeoDashboard'  => __DIR__ . '/module-seo-dashboard',
    'Mageplaza_SeoUltimate'   => __DIR__ . '/module-seo-ultimate',
];

/**
 * Get loader from composer autoload
 * Set Psr-4 namespace for each child module
 */
$vendorDir      = require VENDOR_PATH;
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";
$loader         = require $vendorAutoload;

foreach ($seoMapUltimate as $namespace => $path) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, $namespace, $path);
    $loader->setPsr4(str_replace('_', '\\', $namespace) . '\\', [$path]);
}
