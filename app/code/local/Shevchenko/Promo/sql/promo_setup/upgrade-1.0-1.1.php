<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$ruleProductTable = $installer->getTable('catalogrule/rule');
$columnOptionsSize = array(
    'TYPE' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'SIZE' => 3,
    'NULLABLE'  => false,
    'COMMENT'   => 'Image Promo size',
);
$columnOptionsOpacity = array(
    'TYPE' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'SIZE' => 3,
    'NULLABLE'  => false,
    'COMMENT'   => 'Image Promo opacity',
);
$installer->getConnection()->addColumn($ruleProductTable, 'image_size', $columnOptionsSize);
$installer->getConnection()->addColumn($ruleProductTable, 'image_opacity', $columnOptionsOpacity);
$installer->endSetup();