<?php
/**
 * Created by PhpStorm.
 * User: anastatia
 * Date: 04/02/2020
 * Time: 10:09
 */

namespace Dowell\Forsage\Setup\Patch\Data;


use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateAttributes implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\SchemaSetupInterface
     */
    protected $setup;
    protected $eavSetupFactory;

    private $_attributeRepository;
    private $_attributeOptionManagement;
    private $_option;
    private $_attributeOptionLabel;

    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Model\Entity\Attribute\OptionLabelFactory $attributeOptionLabel,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $option
    )
    {

        $this->setup=$setup;
        $this->eavSetupFactory = $eavSetupFactory;

        /* These are optional and used only to populate the attribute with some mock options */
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->_option = $option;
        $this->_attributeOptionLabel = $attributeOptionLabel;
    }

    public function apply()
    {
        $setup =$this->setup;

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'forsage_id',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Forsage ID',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'note' => 'Add forsage id'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'forsage_id',
            [
                'type'         => 'int',
                'label'        => 'Forsage Id',
                'input'        => 'text',
                'sort_order'   => 100,
                'source'       => '',
                'global'       => 1,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => null,
                'group'        => 'Forsage',
                'backend'      => ''
            ]
        );





    }


    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}