<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = '\Magento\Sales\Model\ResourceModel\Order'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function getData()
    {
        $data = parent::getData();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        if( ($requestInterface->getActionName() == 'gridToCsv') || ($requestInterface->getActionName() == 'gridToXml')){
            if($this->inventoryIsEnable()){
                $options = $objectManager->get('Magestore\InventorySuccess\Model\Warehouse\Options')->toHashOption();
            }
            $optionsFulfill = array('0'=> 'No','1'=>'Yes');
            foreach ($data as &$item) {
                if($this->inventoryIsEnable()){
                    if(isset($item['warehouse_id'])) {
                        $item['warehouse_id'] = $options[$item['warehouse_id']];
                    }
                }
                if(isset($item['fulfill_online'])) {
                    $item['fulfill_online'] = $optionsFulfill[$item['fulfill_online']];
                }
            }
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function inventoryIsEnable(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleManage = $objectManager->get('\Magento\Framework\Module\Manager')->isEnabled('Magestore_InventorySuccess');
        if($moduleManage){
            return true;
        }
        return false;
    }
}
