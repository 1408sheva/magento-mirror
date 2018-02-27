<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$ruleProductTable = $installer->getTable('catalogrule/rule');
$columnOptions = array(
    'TYPE' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'SIZE' => 255,
    'NULLABLE'  => false,
    'COMMENT'   => 'Shevchenko Image Promo',

);
$installer->getConnection()->addColumn($ruleProductTable, 'shevchenko_promo_img', $columnOptions);
$installer->endSetup();