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
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Sales Adminhtml report filter form order
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Magestore_Webpos_Block_Adminhtml_Report_Filter_Form_Order extends Magestore_Webpos_Block_Adminhtml_Report_Filter_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

//        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
//
//            $fieldset->addField('show_actual_columns', 'select', array(
//                'name'       => 'show_actual_columns',
//                'options'    => array(
//                    '1' => Mage::helper('reports')->__('Yes'),
//                    '0' => Mage::helper('reports')->__('No')
//                ),
//                'label'      => Mage::helper('reports')->__('Show Actual Values'),
//            ));
//
//        }

        return $this;
    }
}
