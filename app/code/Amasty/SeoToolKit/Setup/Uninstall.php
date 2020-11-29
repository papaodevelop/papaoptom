<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


declare(strict_types=1);

namespace Amasty\SeoToolKit\Setup;

use Amasty\SeoToolKit\Api\Data\RedirectInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    const TABLES_TO_DROP = [
        RedirectInterface::TABLE_NAME,
        RedirectInterface::STORE_TABLE_NAME,
    ];

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        foreach (self::TABLES_TO_DROP as $table) {
            $connection->dropTable($installer->getTable($table));
        }
    }
}
