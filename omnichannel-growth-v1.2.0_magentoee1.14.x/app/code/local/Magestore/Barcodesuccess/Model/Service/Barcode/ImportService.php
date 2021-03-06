<?php
/**
 *
 *  Magestore
 *   NOTICE OF LICENSE
 *
 *   This source file is subject to the Magestore.com license that is
 *   available through the world-wide-web at this URL:
 *   http://www.magestore.com/license-agreement.html
 *
 *   DISCLAIMER
 *
 *   Do not edit or add to this file if you wish to upgrade this extension to newer
 *   version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Barcodesuccess
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 *
 */

/**
 * Class Magestore_Barcodesuccess_Model_Service_Barcode_ImportService
 */
class Magestore_Barcodesuccess_Model_Service_Barcode_ImportService
{
    const INVALID_FILE_NAME = 'import_product_to_barcode_invalid.csv';

    /**
     * @param $file
     * @return array
     * @throws Exception
     */
    public function importFromCsvFile( $file )
    {
        $helper = Mage::helper('barcodesuccess');
        if ( !isset($file['tmp_name']) ) {
            throw new Exception($helper->__('Invalid file upload attempt.'));
        }
        $csvObject            = new Varien_File_Csv();
        $importProductRawData = $csvObject->getData($file['tmp_name']);
        $fileFields           = $importProductRawData[0];
        $validFields          = $this->_filterFileFields($fileFields);
        $invalidFields        = array_diff_key($fileFields, $validFields);
        $importProductData    = $this->_filterImportProductData($importProductRawData, $invalidFields, $validFields);
        $dataToImport         = array();
        $invalidData          = array($this->getRequiredCsvFields());

        if ( !count($importProductData) ) {
            $invalidData = $importProductRawData;
        }

        $totalQty = 0;
        foreach ( $importProductData as $rowIndex => $dataRow ) {
            // skip headers
            if ( $rowIndex == 0 ) {
                continue;
            }
            /** check barcode */
            $barcode           = $dataRow[1];
            $barcodeCollection = Mage::getModel('barcodesuccess/barcode')->getCollection()
                                     ->addFieldToFilter(Magestore_Barcodesuccess_Model_Barcode::BARCODE, $barcode);
            if ( $barcodeCollection->getSize() ) {
                $invalidData[] = $dataRow;
                continue;
            }
            /** check sku */
            $productSku   = $dataRow[0];
            $productModel = Mage::getModel('catalog/product')->getCollection()
                                ->addFieldToFilter('sku', $productSku)
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
            if ( $barcode
                 && $productModel->getId()
                 && isset($dataRow[2])
                 && is_numeric($dataRow[2])
                 && $dataRow[2]
                 && $dataRow[2] > 0
            ) {
                /** Set time to GMT */
                if ( $dataRow[4] ) {
                    $dataRow[4] = Mage::getModel('core/date')->gmtDate(null, $dataRow[4]);
                }
                $barcodeData    = array(
                    Magestore_Barcodesuccess_Model_Barcode::PRODUCT_ID     => $productModel->getId(),
                    Magestore_Barcodesuccess_Model_Barcode::PRODUCT_SKU    => $productSku,
                    Magestore_Barcodesuccess_Model_Barcode::QTY            => $dataRow[2],
                    Magestore_Barcodesuccess_Model_Barcode::BARCODE        => $dataRow[1],
                    Magestore_Barcodesuccess_Model_Barcode::SUPPLIER_CODE  => $dataRow[3],
                    Magestore_Barcodesuccess_Model_Barcode::PURCHASED_TIME => $dataRow[4] ? $dataRow[4] : date('Y-m-d H:i:s'),
                );
                $dataToImport[] = $barcodeData;
                $totalQty += $dataRow[2];
            } else {
                $invalidData[] = $dataRow;
            }
        }
        if ( count($invalidData) > 1 ) {
            $this->createInvalidFile($invalidData);
        }
        $this->_getSession()->setData('total_qty_import', $totalQty);
        return $dataToImport;
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields( array $fileFields )
    {
        $filteredFields    = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        // process title-related fields that are located right after required fields with store code as field name)
        for ( $index = $requiredFieldsNum; $index < count($fileFields); $index++ ) {
            $titleFieldName         = $fileFields[$index];
            $filteredFields[$index] = $titleFieldName;
        }
        return $filteredFields;
    }

    /**
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return array('SKU', 'BARCODE', 'QTY', 'SUPPLIER', 'PURCHASED TIME');
    }

    /**
     * @param array $productRawData
     * @param array $invalidFields
     * @param array $validFields
     * @return array
     * @throws Exception
     */
    protected function _filterImportProductData(
        array $productRawData,
        array $invalidFields,
        array $validFields
    ) {
        $validFieldsNum = count($validFields);
        foreach ( $productRawData as $rowIndex => $dataRow ) {
            // skip empty rows
            if ( count($dataRow) <= 1 ) {
                unset($productRawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ( $dataRow as $fieldIndex => $fieldValue ) {
                if ( isset($invalidFields[$fieldIndex]) ) {
                    unset($productRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if ( count($productRawData[$rowIndex]) != $validFieldsNum ) {
                $this->_getSession()->addError(Mage::helper('barcodesuccess')->__('Invalid file format.'));
                return array();
            }
        }
        return $productRawData;
    }

    /**
     * @param $invalidData
     * @return $this
     */
    protected function createInvalidFile(
        $invalidData
    ) {
        /** Create file */
        $uploader = new Varien_File_Csv();
        if ( !file_exists(Mage::getBaseDir('media') . DS . 'barcodesuccess') ) {
            mkdir(Mage::getBaseDir('media') . DS . 'barcodesuccess');
        }
        $fileDir = Mage::getBaseDir('media') . DS . 'barcodesuccess' . DS . self::INVALID_FILE_NAME;
        $uploader->saveData($fileDir, $invalidData);
        /** add Message */
        Mage::getSingleton('adminhtml/session')->setData('error_import', true);
        Mage::getSingleton('adminhtml/session')->setData('sku_invalid', count($invalidData) - 1);
        return $this;
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}