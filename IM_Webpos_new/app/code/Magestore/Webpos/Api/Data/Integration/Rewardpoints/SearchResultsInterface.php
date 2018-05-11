<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Integration\Rewardpoints;

interface SearchResultsInterface
{
    /**
     * Get items list.
     *
     * @return \Magestore\Webpos\Api\Data\Integration\Rewardpoints\PointInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Magestore\Webpos\Api\Data\Integration\Rewardpoints\PointInterface[] $items
     * @return $this
     */
    public function setItems(array $items);


    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount();

}