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
 * @package     Magestore_Purchaseordersuccess
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */


/**
 * Purchaseordersuccess Adminhtml Block
 *
 * @category    Magestore
 * @package     Magestore_Purchaseordersuccess
 * @author      Magestore Developer
 */
class Magestore_Purchaseordersuccess_Block_Adminhtml_Return_Edit_Tab_Transferreditem_Grid
    extends Magestore_Purchaseordersuccess_Block_Adminhtml_Abstractgrid
{
    /**
     * @var Magestore_Purchaseordersuccess_Model_Return
     */
    protected $returnRequest;

    public function __construct()
    {
        parent::__construct();
        $this->setId('return_request_transferred_item_list');
        $this->setDefaultSort('transferred_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->returnRequest = Mage::registry('current_return_request');
    }

    /**
     * Prepare collection for grid product
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $ids = $this->returnRequest->getItems()->getAllIds();
        /** @var Magestore_Purchaseordersuccess_Model_Mysql4_Return_Item_Transferred_Collection $collection */
        $collection = Mage::getResourceModel('purchaseordersuccess/return_item_transferred_collection')
            ->addFieldToFilter('main_table.return_item_id', array('in' => $ids));
        $collection->getSelect()->joinLeft(
            array('return_item' => Mage::getSingleton('core/resource')->getTableName('os_return_order_item')),
            'main_table.return_item_id = return_item.return_item_id',
            array('product_sku', 'product_name', 'product_supplier_sku')
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Transferred item grid columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transferred_at',
            array(
                'header' => $this->__('Delivery Date'),
                'align' => 'left',
                'index' => 'transferred_at',
                'type' => 'date',
                'filter_condition_callback' => array($this, '_filterDateCallback')
            )
        )->addColumn("product_sku",
            array(
                "header" => $this->__("Product SKU"),
                "index" => "product_sku",
                "sortable" => true,
            )
        )->addColumn("product_name",
            array(
                "header" => $this->__("Product Name"),
                "index" => "product_name",
                "sortable" => true,
            )
        )->addColumn("product_supplier_sku",
            array(
                "header" => $this->__("Supplier SKU"),
                "index" => "product_supplier_sku",
                "sortable" => true,
            )
        )->addColumn("qty_transferred",
            array(
                "header" => $this->__("Delivered Qty"),
                "index" => "qty_transferred",
                "sortable" => true,
                "type"  => 'number',
                'filter_condition_callback' => array($this, '_filterQtyCallback')
            )
        )->addColumn("created_by",
            array(
                "header" => $this->__("Created By"),
                "index" => "created_by",
                "sortable" => true,
            )
        );

        $this->addExportType('*/purchaseordersuccess_return_transferreditem/exportCsv', $this->__('CSV'));
        $this->addExportType('*/purchaseordersuccess_return_transferreditem/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            "*/purchaseordersuccess_return_transferreditem/grid",
            array("_current" => true, 'supplier_id' => $this->returnRequest->getSupplierId())
        );
    }

    /**
     * Apply `qty` filter to product grid.
     *
     * @param $collection
     * @param $column
     */
    protected function _filterQtyCallback($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        if (isset($value['from']))
            $collection->addFieldToFilter('main_table.qty_transferred', array('gteq' => $value['from']));
        if (isset($value['to']))
            $collection->addFieldToFilter('main_table.qty_transferred', array('lteq' => $value['to']));
        return $collection;
    }
}