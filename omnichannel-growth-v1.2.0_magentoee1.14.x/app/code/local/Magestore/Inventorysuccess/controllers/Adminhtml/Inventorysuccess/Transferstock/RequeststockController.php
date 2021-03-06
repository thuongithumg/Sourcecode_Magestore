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
 * @package     Magestore_Inventorysuccess
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */


/**
 * Class Magestore_Inventorysuccess_Adminhtml_Inventorysuccess_Transferstock_RequeststockController
 */
class Magestore_Inventorysuccess_Adminhtml_Inventorysuccess_Transferstock_RequeststockController
    extends
    Mage_Adminhtml_Controller_Action
{
    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        $resource = 'inventorysuccess/view_transferstock/view_requeststock';
        switch ( $this->getRequest()->getActionName() ) {
            case 'new' :
            case 'save':
                $resource = 'inventorysuccess/create_transferstock/create_requeststock';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($resource);
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('inventorysuccess/view_transferstock/view_requeststock');
        return $this->renderLayout();
    }

    /**
     * render ajax view
     * @return Mage_Core_Controller_Varien_Action
     */
    public function gridAction()
    {
        return $this->loadLayout()->renderLayout();
    }

    /**
     * render ajax view
     * @return Mage_Core_Controller_Varien_Action
     */
    public function returnAction(){
        return $this->loadLayout()->renderLayout();
    }

    /**
     *
     */
    public function newAction()
    {
        $this->renderForm();
    }

    /**
     *
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('inventorysuccess/transferstock')->load($id);
        if($model->getId()){
            Mage::getSingleton('inventorysuccess/transferstock_shortfallValidation')->_showNoticeShortfall($id,$model->getType());
        }
        $this->renderForm();
    }

    protected function renderForm()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Magestore_Inventorysuccess_Model_Transferstock $model */
        $model = Mage::getModel('inventorysuccess/transferstock')->load($id);
        if ( $model->getId() || !$id ) {
            $data = $this->_getSession()->getFormData(true);
            if ( !empty($data) ) {
                $model->setData($data);
            }
            if ( !$id ) {
                $model->setData('transferstock_code',
                                Magestore_Coresuccess_Model_Service::incrementIdService()->getNextCode(Magestore_Inventorysuccess_Model_Transferstock::TRANSFER_CODE_PREFIX));
            }
            Mage::register('requeststock_data', $model);
            $this->_initEditForm();
            if ( $model->getId() ) {
                $this->_title($this->__('Request Stock #%s', $model->getData('transferstock_code')));
            } else {
                $this->_title($this->__('New Stock Request'));
            }
            $this->renderLayout();
        } else {
            $this->_getSession()->addError(
                Mage::helper('inventorysuccess')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * @return $this
     */
    protected function _initEditForm()
    {
        $this->loadLayout();
        $this->_setActiveMenu('inventorysuccess/create_transferstock/create_requeststock');
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Request Stock'),
            Mage::helper('adminhtml')->__('Request Stock')
        );
        $this->_title($this->__('InventorySuccess'))
             ->_title($this->__('Request Stock'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_edit'))
             ->_addLeft($this->getLayout()->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_edit_tabs'));
        return $this;
    }

    /**
     *
     */
    public function saveAction()
    {
        $step = $this->getRequest()->getParam('step');
        switch ( $step ) {
            case "save_general" :
                $this->_saveGeneral();
                break;
            case "start_request":
                $this->_startRequest();
                break;
            case "complete":
                $this->_complete();
                break;
            case "save_delivery":
                $this->_saveDelivery();
                break;
            case "save_receiving":
                $this->_saveReceiving();
                break;
            case "return_items":
                $this->_returnItems();
                break;
            case "send_mail":
                $this->_sendMail();
                break;
            case "cancel_transfer":
                $this->cancelTransfer();
                break;
            case "re_open":
                $this->reopenTransfer();
                break;
            case "delete_transfer":
                $this->deleteTransfer();
                break;
            case "return_items_by_send_stock":
                $this->_returnItemsBySendStock();
                break;
            default:
                $this->_save();
        }
    }

    public function reopenTransfer(){
        $id = $this->getRequest()->getParam('id');
        $transfer = $this->_getCurrentTransfer();
        $transfer->setStatus(Magestore_Inventorysuccess_Model_Transferstock::STATUS_PENDING)->save();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Stock transfer #"%s" has been re-opened.',$transfer->getTransferstockCode()));
        return $this->_redirect('*/*/edit', array('id' => $id));
    }

    public function deleteTransfer(){
        $id = $this->getRequest()->getParam('id');
        $transfer = $this->_getCurrentTransfer();
        $transfer->delete();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Stock transfer #"%s" has been deleted.',$transfer->getTransferstockCode()));
        return $this->_redirect('*/*/index');
    }
    /**
     * @return mixed
     */
    public function cancelTransfer(){
        $id = $this->getRequest()->getParam('id');
        $transfer = $this->_getCurrentTransfer();
        $transfer->setStatus(Magestore_Inventorysuccess_Model_Transferstock::STATUS_CANCELED)->save();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Stock transfer #"%s" has been canceled.',$transfer->getTransferstockCode()));
        return $this->_redirect('*/*/edit', array('id' => $id));
    }
    /**
     * Process send mail
     */
    public function _sendMail(){
        $id = $this->getRequest()->getParam('id');
        $transfer = $this->_getCurrentTransfer();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('The email has been sent successfully.'));
        return  $this->_redirect('*/inventorysuccess_transferstock_sendmail/execute',array('id' => $id,'type' => $transfer->getType()));
    }

    /**
     * Process step 1
     */
    protected function _saveGeneral()
    {
        if ( $data = $this->getRequest()->getPost() ) {
            /** Validate Data */
            $validateResult = $this->_getService()->validateTranferGeneralForm($data);
            if ( !$validateResult['is_validate'] ) {
                foreach ( $validateResult['errors'] as $error ) {
                    $this->_getSession()->addError($error);
                }
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => null));
                return $this;
            }
            /** @var Magestore_Inventorysuccess_Model_Transferstock $model */
            $model        = Mage::getModel('inventorysuccess/transferstock');
            $data['type'] = Magestore_Inventorysuccess_Model_Transferstock::TYPE_REQUEST;
            $this->_getService()->initTransfer($model, $data);
            try {
                $model->getResource()->save($model);
                $this->_getSession()->setFormData(false);
                $this->_getSession()->setData('request_products', false);
                $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('General information has been saved successfully.'));
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return $this;
            } catch ( Exception $e ) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => null));
                return $this;
            }
        }
        $this->_getSession()->addError(
            Mage::helper('inventorysuccess')->__('Unable to find item to save')
        );
        return $this;
    }

    /**
     * @return $this
     */
    protected function _save()
    {
        $transfer    = $this->_getCurrentTransfer();
        $productlist = $this->_getSelectedProducts();
        /** save general information first */
        $transfer->setData(Magestore_Inventorysuccess_Model_Transferstock::NOTIFIER_EMAILS, $this->getRequest()->getParam('notifier_emails'));
        $transfer->setData(Magestore_Inventorysuccess_Model_Transferstock::REASON, $this->getRequest()->getParam('reason'));
        $transfer->save();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('General information has been saved successfully.'));
        /** save products */
        if ( !count($productlist) ) {
//            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__("There is no product to request."));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return $this;
        }
        try {
            $this->_getService()->setProducts($transfer, $productlist);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Stock transfer has been successfully saved.'));
        } catch ( \Exception $e ) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_getSession()->setFormData(false);
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        return $this;
    }

    /**
     * // -> change status to procesing
     * // -> saved product
     * // -> send email notification
     * @return $this
     */
    protected function _startRequest()
    {
        $transfer = $this->_getCurrentTransfer();
        $products = $this->_getSelectedProducts();
        if ( !count($products) ) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__("There is no product to request."));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return $this;
        }
        $this->_getService()->saveTransferStockProduct($transfer, $products);
        $transfer->setStatus(Magestore_Inventorysuccess_Model_Transferstock::STATUS_PROCESSING);
        $transfer->save();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Request stock #' . $transfer->getTransferstockCode() . ' is ready to deliver and receive!'));
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        return $this;
    }

    /**
     * @return $this
     */
    protected function _saveDelivery()
    {
        $transfer = $this->_getCurrentTransfer();
        $products = $this->getRequest()->getParam('products');
        $products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($products['delivery']);
        $this->_getSession()->setData('request_delivery_products', $products);

        /** refine products array('product_id'=>[]) */
        $refinedProducts = array();
        foreach ( $products as $product ) {
//            if(!isset($product['new_qty'])){
//                continue;
//            }
            if($product['new_qty'] > max($product['qty'] - $product['qty_delivered'],0)){
                $product['qty'] = max($product['qty'] - $product['qty_delivered'],0);
            }else{
                $product['qty'] = $product['new_qty'];
            }
            if (!isset($product['new_qty']) || $product['new_qty'] <= 0 || $product['new_qty'] == '') {
                continue;
            }
            unset($product['new_qty']);
            unset($product['qty_delivered']);
            $refinedProducts[$product['product_id']] = $product;
        }
        if ( !count($refinedProducts) ) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__("There is no product to deliver."));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return $this;
        }
        $isValid = $this->_getService()->validateStockDelivery($transfer, $refinedProducts);
        if ( $isValid ) {
            $this->_getService()->saveTransferActivityProduct($transfer, $refinedProducts,
                                                              Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_DELIVERY);
            $this->_getSession()->setData('request_delivery_products', null);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Create delivery successfully!'));
        }

        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        return $this;
    }

    /**
     *
     */
    public function returnAllAction()
    {
        $transferStock = $this->_getCurrentTransfer();
        try {
            Magestore_Coresuccess_Model_Service::transferStockService()->returnItems($transferStock);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Return items successfully.'));
        } catch (\Exception $e) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('An error occurred while return items'));
        }
        return $this->_redirect('*/*/edit', array('id' => $transferStock->getId()));
    }

    /**
     *
     */
    protected function _returnItems()
    {
        $transferStock = $this->_getCurrentTransfer();
        $items = $this->getRequest()->getParam('returning_items');
        $items = Mage::helper('adminhtml/js')->decodeGridSerializedInput($items);
        $returningItem = array();
        foreach ($items as $key => $value) {
            $maxReturn = $value['qty_delivered'] - ($value['qty_received'] + $value['qty_returned']);
            if($maxReturn<=0){
                $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('Unable to return product %s.',$value['product_name']));
            }
            if($value['new_qty']>$maxReturn)
                $value['new_qty'] = $maxReturn;
            if (!isset($value['new_qty']) || $value['new_qty'] <= 0 || $value['new_qty'] == '') {
                continue;
            }
            $returningItem[$key] = $value['new_qty'];
        }
        if (empty($returningItem)) {
            if(!count($items))
                $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('Please return at least one item.'));
            return $this->_redirect('*/*/edit', array('id' => $transferStock->getId()));
        }
        try {
            Magestore_Coresuccess_Model_Service::transferStockService()->returnItems($transferStock, $returningItem);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Return items successfully.'));
        } catch (\Exception $e) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('An error occurred while return items'));
        }
        return $this->_redirect('*/*/edit', array('id' => $transferStock->getId()));
    }

    protected function _returnItemsBySendStock()
    {
        $transfer = $this->_getCurrentTransfer();
        // create return items
        $items = $this->getRequest()->getParam('returning_items');
        $items = Mage::helper('adminhtml/js')->decodeGridSerializedInput($items);
        foreach ($items as $key => $value) {
            $maxReturn = $value['qty_delivered'] - ($value['qty_received'] + $value['qty_returned']);
            if($maxReturn<=0){
                $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('Unable to return product %s.',$value['product_name']));
            }
            if($value['new_qty']>$maxReturn)
                $value['new_qty'] = $maxReturn;
            if (!isset($value['new_qty']) || $value['new_qty'] <= 0 || $value['new_qty'] == '')
                continue;
            $returningItem[$key] = $value['new_qty'];
        }
        if (empty($returningItem)) {
            if(!count($items))
                $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Please return at least one item.'));
            return $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        }

        // get send stock products data
        $products = array();

        foreach ($items as $key => $_item){
            $products[$key] = array(
                'product_id' => $_item['product_id'],
                'product_sku' => $_item['product_sku'],
                'product_name' => $_item['product_name'],
                'qty' => $_item['new_qty']
            );
        }
        if (!count($products)) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__("There is no product to return."));
            $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
            return $this;
        }

        // create new transferstock
        $data = array(
            'transferstock_code' => Magestore_Coresuccess_Model_Service::incrementIdService()->getNextCode(Magestore_Inventorysuccess_Model_Transferstock::TRANSFER_CODE_PREFIX),
            'source_warehouse_id' => $transfer->getData('des_warehouse_id'),
            'des_warehouse_id' => $transfer->getData('source_warehouse_id'),
            'notifier_emails' => '',
            'reason' => Mage::helper('inventorysuccess')->__('Return item from transferstock %s',$transfer->getData('transferstock_code')),
            'type' =>  Magestore_Inventorysuccess_Model_Transferstock::TYPE_SEND
        );
        /** @var Magestore_Inventorysuccess_Model_Transferstock $model */
        $model = Mage::getModel('inventorysuccess/transferstock');
        $this->_getService()->initTransfer($model, $data);
        try {
            $model->getResource()->save($model);
            $this->_getSession()->setFormData(false);
            $this->_getSession()->setData('send_products', false);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('General information has been saved successfully.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
            return $this;
        }
        Mage::register('new_transferstock_code',$model->getTransferstockCode());
        $this->_getSession()->setData('send_products', $products);
        if ($this->_getService()->saveTransferStockProduct($model, $products, false, false)) {
            $model->setStatus(Magestore_Inventorysuccess_Model_Transferstock::STATUS_PROCESSING);
            $model->save();
            $this->_getService()->saveTransferActivityProduct(
                $model,
                $products,
                Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_DELIVERY,
                false
            );
            /** message */
            $this->_getSession()->setData('send_products', null);
            $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Sent ' . count($products) . ' product(s) to the destination warehouse!'));
        }
        try {
            Magestore_Coresuccess_Model_Service::transferStockService()->returnItemsNotAdjustStock($transfer, $returningItem);
        } catch (\Exception $e) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__('An error occurred while return items'));
        }
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        return $this;
    }

    /**
     *
     */
    protected function _saveReceiving()
    {
        $transfer = $this->_getCurrentTransfer();
        $products = $this->getRequest()->getParam('products');
        $products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($products['receiving']);

        /** Refine products array('product_id' => []) */
        $refinedProducts = array();
        foreach ( $products as $product ) {
            if (!isset($product['new_qty']) || $product['new_qty'] <= 0 || $product['new_qty'] == '') {
                continue;
            }
            if($product['new_qty'] > max($product['qty_delivered'] - $product['qty_received'] - $product['qty_returned'],0)){
                $product['qty'] = max($product['qty_delivered'] - $product['qty_received'] - $product['qty_returned'],0);
            }else{
                $product['qty'] = $product['new_qty'];
            }
            unset($product['new_qty']);
            unset($product['qty_delivered']);
            unset($product['qty_received']);
            unset($product['qty_returned']);
            $refinedProducts[$product['product_id']] = $product;
        }
        if ( !count($refinedProducts) ) {
            $this->_getSession()->addError(Mage::helper('inventorysuccess')->__("There is no product to receive."));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return $this;
        }
        $this->_getService()->saveTransferActivityProduct($transfer, $refinedProducts,
                                                          Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_RECEIVING);
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Receiving has been created successfully.'));
        $this->_redirect('*/*/edit', array('id' => $transfer->getId(), 'activeTab' => 'receiving_history'));
        return $this;
    }

    /**
     * Mark as complete
     * @return $this
     */
    protected function _complete()
    {
        $transfer = $this->_getCurrentTransfer();
        $transfer->setStatus(Magestore_Inventorysuccess_Model_Transferstock::STATUS_COMPLETED);
        $transfer->save();
        $this->_getSession()->addSuccess(Mage::helper('inventorysuccess')->__('Marked stock request #%s as completed', $transfer->getTransferstockCode()));
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getParam('products');
        $products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($products);
        return $products;
    }

    /**
     * @return Magestore_Inventorysuccess_Model_Transferstock
     */
    protected function _getCurrentTransfer()
    {
        $transferId = $this->getRequest()->getParam('id');
        $transfer   = Mage::getModel('inventorysuccess/transferstock')->load($transferId);
        return $transfer;
    }

    /**
     * @return Magestore_Inventorysuccess_Model_Service_Transfer_TransferService
     */
    protected function _getService()
    {
        return Magestore_Coresuccess_Model_Service::transferStockService();
    }

    /**
     *
     */
    public function exportShortfallAction()
    {

        $transferId = $this->getRequest()->getParam('id');
        $fileName   = 'shortfall_list_' . $transferId . '.csv';
        $content    = $this->getLayout()->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_edit_tab_stocksummary')
                           ->setData('is_shortfall', true)->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     *
     */
    public function exportSummaryAction()
    {
        $fileName = 'stock_summary.csv';
        $content  = $this->getLayout()->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_edit_tab_stocksummary')
                         ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     *
     */
    public function exportCsvAction()
    {
        $fileName = 'request_stock.csv';
        $content  = $this->getLayout()->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_grid')->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function productlistAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('requeststock.productlist')
             ->setProductsSelected($this->getRequest()->getPost('products_selected', null));
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function productlistgridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('requeststock.productlist')
             ->setProductsSelected($this->getRequest()->getPost('products_selected', null));
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function stocksummaryAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }


    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function deliveryAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function deliverygridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function deliveryhistorygridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function receivingAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function receivinggridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function receivinghistorygridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function differencesAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function differencesgridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }

    /**
     * @return Mage_Core_Controller_Varien_Action
     */
    public function returnedgridAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }


    /**
     *
     */
    public function downloadsampleAction()
    {
        $fileName = 'import_product_to_send.csv';
        $content  = $this->getLayout()
                         ->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_sample')
                         ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     *
     */
    public function downloadsampleDeliveryAction()
    {
        $fileName = 'import_product_to_delivery.csv';
        $content  = $this->getLayout()
                         ->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_sample')
                         ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     *
     */
    public function downloadsampleReceivingAction()
    {
        $fileName = 'import_product_to_receive.csv';
        $content  = $this->getLayout()
                         ->createBlock('inventorysuccess/adminhtml_transferstock_requeststock_sample')
                         ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     *
     */
    public function importAction()
    {
        $transfer = $this->_getCurrentTransfer();
        if ( $this->getRequest()->isPost() ) {
            try {
                $importHandler = Magestore_Coresuccess_Model_Service::transferImportService();
                $data          = $importHandler->importFromCsvFile($_FILES['import-request'],
                                                                   Magestore_Inventorysuccess_Model_ImportType::TYPE_TRANSFER_STOCK_REQUEST);
                if ( !$this->_getService()->saveTransferStockProduct($transfer, $data, false, false) ) {
//                    $this->_locatorFactory->create()->refreshSessionByKey("request_products");
                } else {
                    $this->_getSession()->addSuccess($this->__('The product transfer has been imported.'));
                }
            } catch
            ( \Exception $e ) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
    }

    /**
     *
     */
    public function importDeliveryAction()
    {
        $transfer = $this->_getCurrentTransfer();
        if ( $this->getRequest()->isPost() ) {
            try {
                $importHandler = Magestore_Coresuccess_Model_Service::transferImportService();
                $data          = $importHandler->importFromCsvFile($_FILES['import-request-delivery'],
                                                                   Magestore_Inventorysuccess_Model_ImportType::TYPE_TRANSFER_STOCK_REQUEST_DELIVERY);
                $this->_getService()->saveTransferActivityProduct($transfer, $data,
                                                                  Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_DELIVERY);
                $this->_getSession()->addSuccess($this->__('The product delivery has been imported.'));
            } catch
            ( \Exception $e ) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
    }

    /**
     *
     */
    public function importReceivingAction()
    {
        $transfer = $this->_getCurrentTransfer();
        if ( $this->getRequest()->isPost() ) {
            try {
                $importHandler = Magestore_Coresuccess_Model_Service::transferImportService();
                $data          = $importHandler->importFromCsvFile($_FILES['import-request-receiving'],
                                                                   Magestore_Inventorysuccess_Model_ImportType::TYPE_TRANSFER_STOCK_REQUEST_RECEIVING);
                $this->_getService()->saveTransferActivityProduct($transfer, $data,
                                                                  Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_RECEIVING);
                $this->_getSession()->addSuccess($this->__('The product receiving has been imported.'));
            } catch
            ( \Exception $e ) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/edit', array('id' => $transfer->getId()));
    }

    /**
     *
     */
    public function downloadInvalidAction()
    {
        $fileName = Magestore_Inventorysuccess_Model_ImportType::INVALID_TRANSFER_STOCK_REQUEST;
        $this->_prepareDownloadResponse($fileName,
                                        file_get_contents(Mage::getBaseDir('media') . DS . 'inventorysuccess' . DS . $fileName));
    }

    /**
     *
     */
    public function downloadInvalidDeliveryAction()
    {
        $fileName = Magestore_Inventorysuccess_Model_ImportType::INVALID_TRANSFER_STOCK_REQUEST_DELIVERY;
        $this->_prepareDownloadResponse($fileName,
                                        file_get_contents(Mage::getBaseDir('media') . DS . 'inventorysuccess' . DS . $fileName));
    }

    /**
     *
     */
    public function downloadInvalidReceivingAction()
    {
        $fileName = Magestore_Inventorysuccess_Model_ImportType::INVALID_TRANSFER_STOCK_REQUEST_RECEIVING;
        $this->_prepareDownloadResponse($fileName,
                                        file_get_contents(Mage::getBaseDir('media') . DS . 'inventorysuccess' . DS . $fileName));
    }

    /**
     * handle after scan barcode
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function handleBarcodeAction()
    {
        $barcodes        = $this->_getSession()->getData('scan_barcodes', true);
        $transferstockId = $this->getRequest()->getParam('transferstock_id');
        if ( $barcodes ) {
            $transferStock = Mage::getModel('inventorysuccess/transferstock')->load($transferstockId);
            Magestore_Coresuccess_Model_Service::transferStockService()->addProductFromBarcode($transferStock, $barcodes);
        }
        return $this->_redirect('*/*/edit', array('id' => $transferstockId));
    }

    /**
     * handle after scan barcode in delivery tab
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function handleBarcodeDeliveryAction()
    {
        $barcodes        = $this->_getSession()->getData('scan_barcodes', true);
        $transferstockId = $this->getRequest()->getParam('transferstock_id');
        if ( $barcodes ) {
            $transferStock = Mage::getModel('inventorysuccess/transferstock')->load($transferstockId);
            Magestore_Coresuccess_Model_Service::transferStockService()->createTransferActivityFromBarcode($transferStock, $barcodes, Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_DELIVERY);
        }
        return $this->_redirect('*/*/edit', array('id' => $transferstockId));
    }

    /**
     * handle after scan barcode in receving tab
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function handleBarcodeReceivingAction()
    {
        $barcodes        = $this->_getSession()->getData('scan_barcodes', true);
        $transferstockId = $this->getRequest()->getParam('transferstock_id');
        if ( $barcodes ) {
            $transferStock = Mage::getModel('inventorysuccess/transferstock')->load($transferstockId);
            Magestore_Coresuccess_Model_Service::transferStockService()->createTransferActivityFromBarcode($transferStock, $barcodes, Magestore_Inventorysuccess_Model_Transferstock_Activity::ACTIVITY_TYPE_RECEIVING);
        }
        return $this->_redirect('*/*/edit', array('id' => $transferstockId));
    }
}
