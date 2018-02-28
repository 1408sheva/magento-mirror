<?php

class Shevchenko_Promo_Model_Observer
{
    /**
     * Save promo rule image and parameters
     *
     * @throws Exception
     * @return void
     */
    public function adminhtmlControllerCatalogrulePrepareSave()
    {

        if ($data = $_POST) {
            $modelData = [];
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(true);
                    $newDir = "promo";
                    $newdirPath = Mage::getBaseDir('media') . DS . "promo";

                    if (!file_exists($newdirPath)) {
                        mkdir($newdirPath, 0777);
                    }
                    $path = Mage::getBaseDir('media') . DS . $newDir . DS;
                    $image = new Varien_Image(($_FILES['image']['tmp_name']));
                    $x = $image->getOriginalWidth();
                    $y = $image->getOriginalHeight();

                    if ($x < 300 || $y < 300) {
                        throw new Exception( Mage::helper('shevchenko_promo')
                            ->__('The size should be more than 300 pixels'));
                    }
                    $uploadedImg = $uploader->save($path, $_FILES['image']['name']);

                    if ($uploadedImg['file']) {
                        $modelData['shevchenko_promo_img'] = 'promo/' . $uploadedImg['file'];
                    }

                    if ($size = $data['size']) {
                        if ($size < 10 || $size > 100) {
                            throw new Exception( Mage::helper('shevchenko_promo')
                                ->__('Please enter the correct size (from 10 to 100)'));
                        }
                        $modelData['image_size'] = $data['size'];
                    } else {
                        $modelData['image_size'] = 50;
                    }

                    if ($op = $data['opacity']) {
                        if ($op < 0 || $op > 100) {
                            throw new Exception( Mage::helper('shevchenko_promo')
                                ->__('Please enter the correct opacity (from 0 to 100)'));
                        }
                        $modelData['image_opacity'] = $data['opacity'];
                    } else {
                        $modelData['image_opacity'] = 50;
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    throw new Exception($e->getMessage());
                }
            } elseif ($data['image']['delete']) {
                $model = Mage::getModel('catalogrule/rule')->load($data['rule_id']);
                if ($model->getShevchenkoPromoImg() == $data['image']['value']) {
                    $modelData['shevchenko_promo_img'] = '';
                    $modelData['image_size'] = '';
                    $modelData['image_opacity'] = '';
                    Mage::getModel('catalog/product_image')->clearCache();
                }
            }
            if ($modelData) {
                $model = Mage::getModel('catalogrule/rule')->load($data['rule_id']);
                $model->addData($modelData);
                $model->save();
            }
        }
    }


    public function adminhtmlBlockHtmlBefore($observer){

        $block = $observer->getEvent()->getBlock();
        if (!isset($block)) {
            return $this;
        }
        if ($block->getType() == 'adminhtml/promo_catalog_edit_tab_actions') {
            $model = Mage::registry('current_promo_catalog_rule');
            $form = $block->getForm();
            //create new custom fieldset 'website'
            $fieldset = $form->addFieldset('action_fieldset2', array(
                    'legend' => Mage::helper('shevchenko_promo')->__('Image for the rule'),                )
            );
            //create new custom fieldset 'website'
            //Custom Image
            $fieldset->addField('shevchenko_promo_img', 'image', array(
                'label'     => Mage::helper('shevchenko_promo')->__('Image'),
                'required'  => false,
                'name'      => 'image',
            ));

            $fieldset->addField('image_size', 'text', array(
                'label'     => Mage::helper('shevchenko_promo')->__('Size percent'),
                'name'      => 'size',
                'value'  => '50',
                'after_element_html' => '<small>' . Mage::helper('shevchenko_promo')
                        ->__('Specify the percentage of the image (from 10 to 100)') . '</small>',
            ));

            $fieldset->addField('image_opacity', 'text', array(
                'label'     => Mage::helper('shevchenko_promo')->__('Opacity percent'),
                'name'      => 'opacity',
                'after_element_html' =>  '<small>' . Mage::helper('catalogrule')
                        ->__('Specify opacity in the percentage') . '</small>',
            ));
            $form->setValues($model->getData());
        }
        return $this;
    }
}
