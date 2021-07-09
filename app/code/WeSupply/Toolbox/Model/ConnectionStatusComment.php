<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

    use Magento\Config\Model\Config\CommentInterface;
    use Magento\Framework\Phrase;
    use WeSupply\Toolbox\Helper\Data as Helper;

    /**
     * Class ClientNameComment
     * @package WeSupply\Toolbox\Model
     */
    class ConnectionStatusComment implements CommentInterface
    {
        /**
         * @var Helper
         */
        public $helper;
        /**
         * ClientNameComment constructor.
         * @param Helper $helper
         */
        public function __construct(
            Helper $helper
        ) {
            $this->helper = $helper;
        }
        /**
         * @param string $elementValue
         * @return Phrase|string
         */
        public function getCommentText($elementValue)
        {
            if (
                $this->helper->getWeSupplyApiClientId() === null ||
                $this->helper->getWeSupplyApiClientSecret() === null
            ) {
                return __('Please fill in and test your WeSupply credentials');
            }
            if ($this->helper->getConnectionStatusByScope() === null) {
                return __('Please test and save account credentials');
            }
            if (
                !$this->helper->getWeSupplyApiClientId() ||
                !$this->helper->getWeSupplyApiClientSecret() ||
                $this->helper->getConnectionStatusByScope() === 0
            ) {
                return __('Please update and test your WeSupply credentials!');
            }
            return 'WeSupply credentials validator.';
        }
    }
