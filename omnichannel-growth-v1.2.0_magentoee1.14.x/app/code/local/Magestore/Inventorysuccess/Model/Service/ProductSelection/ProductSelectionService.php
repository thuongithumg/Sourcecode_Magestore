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
 * Inventorysuccess Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorysuccess
 * @author      Magestore Developer
 */
class Magestore_Inventorysuccess_Model_Service_ProductSelection_ProductSelectionService
    extends Magestore_Coresuccess_Model_Service_ProductSelection_ProductSelectionService
{
    /**
     * @var Magestore_Inventorysuccess_Model_Service_IncrementIdService 
     */
    protected $incrementIdService;    
    
    /**
     * 
     */
    public function __construct()
    {
        $this->incrementIdService = Magestore_Coresuccess_Model_Service::incrementIdService();
        parent::__construct();
    }
   
    /**
     * Generate an unique code for Selection
     * 
     * @param string $selectionCode
     * @return string
     */
    public function generateUniqueCode($selectionCode)
    {
        return $this->incrementIdService->getNextCode($selectionCode);
    }

}