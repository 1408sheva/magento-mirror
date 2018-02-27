<?php

class Shevchenko_Promo_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Checks whether the rule is applied and return ruleId
     *
     * @param $product
     * @return string
     */
    public function checkRuleApply($product)
    {
        $result = '';
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('catalogrule_product');
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $count = $connection->fetchOne('SELECT COUNT(1) as c FROM ' . $tableName . ' WHERE product_id = ' . $product->getId());
        if ($count) {
            $ruleId = $connection->fetchOne('SELECT rule_id FROM ' . $tableName . ' WHERE product_id = ' . $product->getId() .
                ' AND ' . ' customer_group_id = ' . $groupId);

            $storeId = $product->getStoreId();
            $siteId = Mage::app()->getStore($storeId)->getWebsiteId();
            $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice($date, $siteId, $groupId, $product->getId());
            $result = $rulePrice ? $ruleId : '';
        }
        return $result;
    }
}