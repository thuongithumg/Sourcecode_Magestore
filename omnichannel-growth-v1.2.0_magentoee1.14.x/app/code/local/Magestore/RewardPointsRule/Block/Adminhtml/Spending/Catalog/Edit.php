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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Spending Catalog Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'rewardpointsrule';
        $this->_controller = 'adminhtml_spending_catalog';
        
        $this->_updateButton('save', 'label', Mage::helper('rewardpointsrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('rewardpointsrule')->__('Delete Rule'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleSimpleAction(){
                if ($('rule_simple_action').value == 'fixed') {
                    $('rule_money_step').up(1).hide();
                    $('rule_max_points_spended').up(1).hide();
                } else {
                    $('rule_money_step').up(1).show();
                    $('rule_max_points_spended').up(1).show();
                }
            }
            //Hai.Tran 12/11/2013
            function toggleMaxPriceSpend(){
                if($('rule_max_price_spended_type').value == 'none'){
                    $('rule_max_price_spended_value').up(1).hide();
                }else{
                    $('rule_max_price_spended_value').up(1).show();
                }
            }
            Event.observe(window, 'load', function(){toggleSimpleAction(); toggleMaxPriceSpend();});
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
        ";
    }

    /**
     * get text to show in header when edit an item
     * 
     * @return type
     */
    public function getHeaderText()
    {
        if( Mage::registry('rule_data') && Mage::registry('rule_data')->getId() ) {
            return Mage::helper('rewardpointsrule')->__("Edit Rule '%s'", $this->htmlEscape(Mage::registry('rule_data')->getName()));
        } else {
            return Mage::helper('rewardpointsrule')->__('Add Rule');
        }
    }
}
