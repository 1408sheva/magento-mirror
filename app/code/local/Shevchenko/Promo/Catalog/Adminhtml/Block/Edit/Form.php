<?php

class Shevchenko_Promo_Catalog_Adminhtml_Block_Edit_Form extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        return $this;
    }
}
