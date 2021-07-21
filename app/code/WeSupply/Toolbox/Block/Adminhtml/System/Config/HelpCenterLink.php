<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class OrderTrackingNotifications
 *
 * @package WeSupply\Toolbox\Block\Adminhtml\System\Config
 */
class HelpCenterLink extends Fieldset
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        switch ($element->getId()) {
            case 'help_center_order_tracking_notification_order_tracking_notification':
                $html = '<h3 class="help-center-title">Order Tracking &amp; Notifications</h3>';
                $html .= '<iframe id="order_tracking_notification" class="help-center" src="https://wesupplylabs.com/magento-order-tracking-and-notifications/?id=' . uniqid() .'" ></iframe>';
                break;
            case 'help_center_returns_rma_returns_rma':
                $html = '<h3 class="help-center-title">Returns &amp; RMA</h3>';
                $html .= '<iframe id="returns_rma" class="help-center" src="https://wesupplylabs.com/magento-returns/?id=' . uniqid() .'" ></iframe>';
                break;
            case 'help_center_estimated_delivery_dates_estimated_delivery_dates':
                $html = '<h3 class="help-center-title">Estimated Delivery Dates</h3>';
                $html .= '<iframe id="estimated_delivery_dates" class="help-center" src="https://wesupplylabs.com/magento-estimated-delivery-dates/?id=' . uniqid() .'" ></iframe>';
                break;
            case 'help_center_store_locator_store_locator':
                $html = '<h3 class="help-center-title">Store Locator</h3>';
                $html .= '<iframe id="store_locator" class="help-center" src="https://wesupplylabs.com/magento-store-locator/?id=' . uniqid() .'" ></iframe>';
                break;
            case 'help_center_store_pickup_curbside_store_pickup_curbside':
                $html = '<h3 class="help-center-title">Store Pickup &amp; Curbside</h3>';
                $html .= '<iframe id="store_pickup_curbside" class="help-center" src="https://wesupplylabs.com/magento-store-pickup-curbside/?id=' . uniqid() .'" ></iframe>';
                break;
            case 'help_center_reviews_reviews':
                $html = '<h3 class="help-center-title">Reviews</h3>';
                $html .= '<iframe id="reviews" class="help-center" src="https://wesupplylabs.com/magento-reviews/?id=' . uniqid() .'" ></iframe>';
                break;
            default:
                $html = '';
        }

        return $html;
    }
}
