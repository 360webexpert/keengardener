<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Add;

use Ess\M2ePro\Model\Listing;
use Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Add\SourceMode;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Add\DeleteAll
 */
class DeleteAll extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\Product\Add
{
    //########################################

    public function execute()
    {
        $listing = $this->getListing();

        $ids = array_map('intval', $listing->getChildObject()->getAddedListingProductsIds());
        if (empty($ids)) {
            return $this->_redirect('*/*/', ['_current' => true]);
        }

        $collection = $this->ebayFactory->getObject('Listing\Product')->getCollection();
        $collection->addFieldToFilter('id', ['in' => $ids]);

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->canBeForceDeleted(true);
            $listingProduct->delete();
        }

        $listing->getChildObject()->setData('product_add_ids', $this->getHelper('Data')->jsonEncode([]));
        $listing->save();

        if ($listing->getSetting('additional_data', 'source') == SourceMode::MODE_OTHER) {
            $additionalData = $listing->getSettings('additional_data');
            unset($additionalData['source']);
            $listing->setSettings('additional_data', $additionalData);
            $listing->save();

            return $this->_redirect(
                '*/ebay_listing_other/view',
                [
                    'account'     => $listing->getAccountId(),
                    'marketplace' => $listing->getMarketplaceId(),
                ]
            );
        }

        return $this->_redirect('*/*/', ['_current' => true]);
    }

    //########################################
}
