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
 * @package     Magestore_Reportsuccess
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 *
 *
 * @category    Magestore
 * @package     Magestore_Reportsuccess
 * @author      Magestore Developer
 */

class Magestore_Reportsuccess_Block_Adminhtml_Inventoryreport_Warehouseslocation extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_template = 'reportsuccess/inventoryreport/warehouselocation.phtml';

    /**
     * @return mixed
     */
    public function getHeaderText()
    {
        return $this->__('Inventory Report - Compare by Warehouse');
    }

    /**
     * @return array
     */
    public function getOptionWarehouses(){
        $helper = Mage::helper('reportsuccess')->inventoryInstalled();
        if($helper) {
            $collection = Mage::getModel('inventorysuccess/warehouse')->getCollection();
            $collection = Magestore_Coresuccess_Model_Service::permissionService()->filterPermission(
                $collection, 'admin/reportsuccess/report/inventoryreport'
            );
            return $collection->getItems();
        }
        return false;
    }

    /**
     * @return array
     */
    public function getTypeReportOptions(){
        $optons = array(
            'available_qty' => "Available Qty",
            'sum_qty_to_ship' => "Qty to Ship",
            'sum_total_qty' => "Qty in Warehouse",
            'qty_in_order' =>"Incoming Stock",
            'total_inv_value'=>"Inventory value",
            'total_retail_value'=>'Retails value',
            'total_profit'=>"Potential Profit",
            'total_profit_margin' => "Profit Margin",
        );
        return $optons;
    }
}