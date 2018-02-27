<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog image helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shevchenko_Promo_Catalog_Helper_Image extends Mage_Catalog_Helper_Image
{

    /**
     * Promomark file path
     *
     * @var string
     */
    protected $_promomark;

    /**
     * Promomark Height
     *
     * @var string
     */
    protected $_promomarkHeight;

    /**
     * Promomark Width
     *
     * @var string
     */
    protected $_promomarkWidth;

    /**
     * Watermark Image opacity
     *
     * @var int
     */
    protected $_watermarkImageOpacity;

    /**
     * Current Product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Initialize Helper to work with Image
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @param mixed $imageFile
     * @return Mage_Catalog_Helper_Image
     */
    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile = null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('catalog/product_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setProduct($product);

        $this->setWatermark(
            Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image")
        );
        $this->setWatermarkImageOpacity(
            Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity")
        );
        $this->setWatermarkPosition(
            Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position")
        );
        $this->setWatermarkSize(
            Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size")
        );

        if ($imageFile) {
            $this->setImageFile($imageFile);
        } else {
            // add for work original size
            $this->_getModel()->setBaseFile($this->getProduct()->getData($this->_getModel()->getDestinationSubdir()));
        }

        $ruleId = Mage::helper('shevchenko_promo')->checkRuleApply($product);
        if ($ruleId) {
            $modelRule = Mage::getModel('catalogrule/rule')->load($ruleId);
            if ($modelRule->getShevchenkoPromoImg()) {
                $pathImg = Mage::getBaseDir('media') . DS . $modelRule->getShevchenkoPromoImg();
                if (!($this->getPromomark())) {
                    $this->setPromomark($pathImg);
                    $this->_watermarkImageOpacity = $modelRule->getImageOpacity();
                    $this->setPromomarkSize($modelRule->getImageSize(), $modelRule->getShevchenkoPromoImg());
                }
            }
        }
        return $this;
    }

    /**
     * Get promomark file name
     *
     * @return string
     */
    protected function getPromomark()
    {
        return $this->_promomark;
    }

    /**
     * Set promomark file path
     *
     * @param string $promomark
     * @return Mage_Catalog_Helper_Image
     */
    protected function setPromomark($promomark)
    {
        $this->_promomark = $promomark;
        $this->_getModel()->setWatermarkFile($promomark);
        return $this;
    }

    /**
     * Set promomark size
     *
     * @param string $size
     * @param string $img
     * @return void
     */
    public function setPromomarkSize($size, $img)
    {
        $image = new Varien_Image(Mage::getBaseDir('media') . DS . $img);
        $width = $image->getOriginalWidth();
        $height = $image->getOriginalHeight();

        $imageProduct = new Varien_Image($this->_model->getBaseFile());
        $prodImgHeight = $imageProduct->getOriginalHeight();
        $prodImgWidth = $imageProduct->getOriginalWidth();

        if ($size) {
            $height *= $size / 100;
            $width *= $size / 100;
        }

        if ($height > $prodImgHeight) {
            $ratio = $prodImgHeight / $height;
            $width *= $ratio;
            $height *= $ratio;
        }
        if ($width > $prodImgWidth) {
            $ratio = $prodImgWidth / $width;
            $width *= $ratio;
            $height *= $ratio;
        }

        $this->_promomarkHeight = $height;
        $this->_promomarkWidth = $width;
    }

    /**
     * Return Image URL
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $model = $this->_getModel();

            if ($this->getImageFile()) {
                $model->setBaseFile($this->getImageFile());
            } else {
                $model->setBaseFile($this->getProduct()->getData($model->getDestinationSubdir()));
            }

            if ($model->isCached()) {
                return $model->getUrl();
            } else {
                if ($this->_scheduleRotate) {
                    $model->rotate($this->getAngle());
                }

                if ($this->_scheduleResize) {
                    $model->resize();
                }

                if ($this->getWatermark()) {
                    $model->setWatermark($this->getWatermark());
                }

                if ($this->getPromomark()) {
                    $model->getImageProcessor()
                        ->setWatermarkPosition('top-right')
                        ->setWatermarkImageOpacity($this->_watermarkImageOpacity)
                        ->setWatermarkWidth($this->_promomarkWidth)
                        ->setWatermarkHeigth($this->_promomarkHeight)
                        ->watermark($this->getPromomark());
                }

                $url = $model->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }
        return $url;
    }
}
