<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @module     Giftvoucher
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Giftvoucher_Block_Adminhtml_Giftvoucher_Import_Form
 */
class Magestore_Giftvoucher_Block_Adminhtml_Giftvoucher_Import_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/processImport'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('profile_fieldset', array('legend' => Mage::helper('giftvoucher')->__('Import Form')));

        $fieldset->addField('filecsv', 'file', array(
            'label' => Mage::helper('giftvoucher')->__('Import File'),
            'title' => Mage::helper('giftvoucher')->__('Import File'),
            'name' => 'filecsv',
            'required' => true,
        ));

        $fieldset->addField('sample', 'note', array(
            'label' => Mage::helper('giftvoucher')->__('Download Sample CSV File'),
            'text' => '<a href="' .
            $this->getUrl('*/*/downloadSample') .
            '" title="' .
            Mage::helper('giftvoucher')->__('Download Sample CSV File') .
            '">import_giftcode_sample.csv</a>'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}