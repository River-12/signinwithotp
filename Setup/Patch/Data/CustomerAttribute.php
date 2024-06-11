<?php

namespace Riverstone\SignInWithOtp\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class CustomerAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;
    /**
     * @var Config
     */
    public $eavConfig;
    /**
     * @var LoggerInterface
     */
    public $logger;
    /**
     * @var Attribute
     */
    public $attributeResource;
    /**
     * @var ModuleDataSetupInterface
     */
    public $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    public $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    public $attributeSetFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     * @param LoggerInterface $logger
     * @param Attribute $attributeResource
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        EavSetupFactory          $eavSetupFactory,
        Config                   $eavConfig,
        LoggerInterface          $logger,
        Attribute                $attributeResource,
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
        $this->attributeResource = $attributeResource;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
//        $setup = $this->moduleDataSetup;
//
//        $setup->startSetup();
//
//        /** @var CustomerSetup $customerSetup */
//        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
//
//        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
//        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
//
//        /** @var $attributeSet AttributeSet */
//        $attributeSet = $this->attributeSetFactory->create();
//        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);


        $customerSetup->addAttribute(Customer::ENTITY, 'is_email_verified', [
            'type' => 'int',
            'label' => 'Email verifed',
            'input' => 'boolean',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0,
            'is_used_in_grid' => false,
        ]);

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_email_verified')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ])
            ->save();
        $customerSetup->addAttribute(Customer::ENTITY, 'is_phone_verified', [
            'type' => 'int',
            'label' => 'Email verifed',
            'input' => 'boolean',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0,
            'is_used_in_grid' => false,
        ]);

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_phone_verified')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ])
            ->save();
        $customerSetup->addAttribute(Customer::ENTITY, 'mobile_phone', [
            'type' => 'text',
            'label' => 'Mobile number',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0,
            'is_used_in_grid' => false,
        ]);

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobile_phone')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ])
            ->save();
        $customerSetup->addAttribute(Customer::ENTITY, 'is_verify_email_sent', [
            'type' => 'int',
            'label' => 'Email verifed',
            'input' => 'boolean',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0,
            'is_used_in_grid' => false,
        ]);

        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_verify_email_sent')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ])
            ->save();
        $setup->endSetup();
    }
    /**
     * @inheritdoc
     */
    public function revert()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

}
