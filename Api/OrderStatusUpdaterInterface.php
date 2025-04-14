<?php

namespace Vendor\CustomOrderProcessing\Api;

interface OrderStatusUpdaterInterface
{
    /**
     * Update order status
     *
     * @param string $orderIncrementId
     * @param string $status
     * @return \Magento\Framework\Webapi\Rest\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($orderIncrementId, $status);
}
