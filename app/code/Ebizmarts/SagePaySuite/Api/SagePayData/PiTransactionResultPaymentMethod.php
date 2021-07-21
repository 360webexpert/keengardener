<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 4:01 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiTransactionResultPaymentMethod extends AbstractExtensibleObject implements PiTransactionResultPaymentMethodInterface
{
    /**
     * @inheritDoc
     */
    public function getCard()
    {
        return $this->_get(self::CARD);
    }

    /**
     * @inheritDoc
     */
    public function setCard($card)
    {
        $this->setData(self::CARD, $card);
    }
}
