<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Framework\Phrase;
use Magento\Config\Model\Config\CommentInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

/**
 * Class WsDomainComment
 * @package WeSupply\Toolbox\Model
 */
class WsDomainComment implements CommentInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * WsDomainComment constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        $commentEl = '';
        $weSupplyDomain = $this->helper->getWeSupplyDomain();
        $weSupplyDomainDefault = $this->helper->getWesupplyDomainDefault();
        $weSupplySubdomain = $this->helper->getClientNameByScope();

        if ($weSupplySubdomain != 'install') {
            if ($this->helper->weSupplyHasDomainAlias()) {
                $commentEl .= '<span id="wesupply_api_integration_wesupply_subdomain">' . $weSupplyDomain . '</span>';
            } else {
                $commentEl .= '<span id="wesupply_api_integration_wesupply_subdomain">' . $weSupplySubdomain . '.' . $weSupplyDomainDefault . '</span>';
            }
        } else {
            $commentEl .= '<span id="wesupply_api_integration_wesupply_subdomain">' . __('Will be displayed after a WeSupply account is connected with this Magento store.') . '</span>';
        }

        $commentEl .= '<span class="comment">';
        $commentEl .= __('The WeSupply URL has two parts:');
        $commentEl .= '<br> - ' . __('a subdomain name you chose when you set up your account, followed by ') . '<strong>' . $weSupplyDomainDefault . '</strong>';
        $commentEl .= '<br> - ' . __('or your <strong>Domain Alias</strong> if you already have setup one in your WeSupply account under Settings -> General -> Custom Domain.');
        $commentEl .= '<br>' . '(' . __('Examples: ') . '<strong>' . 'mycompany.' . $weSupplyDomainDefault . '</strong> or <strong>yourdomainalias.com</strong>).';
        $commentEl .= '</span>';

        return $commentEl;
    }
}
