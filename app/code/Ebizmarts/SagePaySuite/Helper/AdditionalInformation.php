<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;

class AdditionalInformation
{
    /** @var Json */
    private $serializer;

    /** @var Logger */
    private $logger;

    /**
     * AdditionalInformation constructor.
     * @param Json $serializer
     */
    public function __construct(Json $serializer, Logger $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param string Serialized data
     * @return array
     */
    public function getUnserializedData(string $serializedData) : array
    {
        try {
            $additionalInfo = $this->serializer->unserialize($serializedData);
        } catch (\InvalidArgumentException $argumentException) {
            $additionalInfo = [];
            $this->logger->logException($argumentException, [$serializedData, __METHOD__, __LINE__]);
        }

        return $additionalInfo;
    }
}
