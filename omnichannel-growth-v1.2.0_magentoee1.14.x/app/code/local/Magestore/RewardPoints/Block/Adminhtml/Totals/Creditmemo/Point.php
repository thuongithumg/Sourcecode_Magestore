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
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Total Point Spend Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Totals_Creditmemo_Point
    extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    /**
     * add points value into creditmemo total
     *     
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $creditmemo = $totalsBlock->getCreditmemo();
        if ($creditmemo->getRewardpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_earn_label',
                'label' => $this->__('Refund Points Earn'),
                'value' => $creditmemo->getRewardpointsEarn(),
                'is_formated'   => true,
            )), 'subtotal');
        }
        if ($creditmemo->getRewardpointsDiscount()>=0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints',
                'label' => $this->__('Point Discount'),
                'value' => -$creditmemo->getRewardpointsDiscount(),
                'base_value' => -$creditmemo->setRewardpointsBaseDiscount(),
            )), 'subtotal');
        }
    }
}
