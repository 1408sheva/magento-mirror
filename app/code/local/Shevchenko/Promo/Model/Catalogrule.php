<?php

class Shevchenko_Promo_Model_Catalogrule extends Mage_Core_Model_Abstract
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

        $select = $connection->select()->from($tableName, 'rule_id')
            ->where('product_id = ?', $product->getId())
            ->where(' customer_group_id = ?', $groupId);
        $ruleId = $connection->fetchOne($select);
        if ($ruleId) {
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