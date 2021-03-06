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
class Magestore_Purchaseordersuccess_Block_Adminhtml_Return_Edit_Tab_Returnsummary_Importproduct
    extends Magestore_Coresuccess_Block_Adminhtml_Modal_Import_Ajax
{
    /**
     *
     * @var string
     */
    protected $modalId = 'import_product_modal';

    /**
     * @var Magestore_Purchaseordersuccess_Model_Return
     */
    protected $returnRequest;

    /*
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('purchaseordersuccess/return/edit/tab/return_summary/import_product.phtml');
        $this->_prepareNotices();
        $this->returnRequest = Mage::registry('current_return_request');
    }

    /**
     * prepare notification messages
     *
     */
    protected function _prepareNotices()
    {
        $notices = array(
            'invalid_file_message' => $this->__('Invalid file type!'),
            'choose_file_upload_message' => $this->__('Please choose CSV file to import.'),
            'importing_error_message' => $this->__('There was an error while importing.'),
            'upload_failed_message' => $this->__('There was an error attempting to upload the file.'),
            'upload_canceled_message' => $this->__('The upload has been canceled by the user or the browser dropped the connection.'),
        );
        $this->addData($notices);
    }

    /**
     * Get csv sample dowload link
     *
     * @return string
     */
    public function getCsvSampleLink(){
        return $this->getUrl(
            "*/purchaseordersuccess_return_item_import/downloadSample",
            array("_current" => true, 'supplier_id' => $this->returnRequest->getSupplierId())
        );
    }

    /**
     * Get import url
     *
     * @return string
     */
    public function getImportLink(){
        return $this->getUrl(
            "*/purchaseordersuccess_return_item_import/import",
            array("_current" => true, 'supplier_id' => $this->returnRequest->getSupplierId())
        );
    }

    /**
     * @return string
     */
    public function getJsParentObjectName(){
        return $this->getLayout()
            ->createBlock('purchaseordersuccess/adminhtml_return_edit_tab_returnsummary_grid')
            ->getJsObjectName();
    }
}