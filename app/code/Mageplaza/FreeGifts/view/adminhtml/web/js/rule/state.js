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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'Magento_Ui/js/grid/columns/select',
    'mage/translate',
], function (Column, $t) {
    'use strict';
    
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);
            
            switch (record.state) {
                case 'running':
                    label = '<div class="grid-severity-notice">';
                    label += $t('Running');
                    break;
                case 'schedule':
                    label = '<div class="grid-severity-notice" style="background:#e9efdf;color:#37af0c">';
                    label += $t('Schedule');
                    break;
                case 'finished':
                    label = '<div class="grid-severity-minor">';
                    label += $t('Finished');
                    break;
            }
            
            label += '</div>';
            return label;
        }
    });
});