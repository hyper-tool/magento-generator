<?php

namespace Silk\Test\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;


/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0)
        {
            $installer->startSetup();
            $tableName = $setup->getTable('silk_test');
            if ($setup->getConnection()->isTableExists($tableName) == true)
            {
                $installer->getConnection()
                    ->addColumn($installer->getTable('silk_test'),'e_profile_picture', array(
                        'type'      => Table::TYPE_TEXT,
                        'nullable'  => true,
                        'length'    => 255,
                        'comment'   => 'Profile Picture'
                    ));
            }
        }

        $installer->endSetup();

    }

}