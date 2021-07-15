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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Setup;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Backend\Attributes;
use Mageplaza\LayeredNavigation\Model\Category\Attribute\Source\Attributes as SourceAttributes;

/**
 * Class InstallData
 * @package Mageplaza\LayeredNavigation\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Category::ENTITY, 'mp_ln_hide_attribute_ids', [
            'type'       => 'text',
            'label'      => 'Hide Filter Attributes on Layered Navigation',
            'input'      => 'multiselect',
            'source'     => SourceAttributes::class,
            'backend'    => Attributes::class,
            'required'   => false,
            'sort_order' => 100,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'Display Settings',
        ]);

        $setup->endSetup();
    }
}
