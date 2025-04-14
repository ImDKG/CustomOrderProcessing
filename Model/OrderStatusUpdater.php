<?php

namespace Vendor\CustomOrderProcessing\Model;

use Vendor\CustomOrderProcessing\Api\OrderStatusUpdaterInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Webapi\Exception;

class OrderStatusUpdater implements OrderStatusUpdaterInterface
{
    protected $orderRepository;
    protected $response;
    protected $orderFactory;


    public function __construct(
        OrderFactory $orderFactory,
        Response $response
    ) {
        $this->response = $response;
        $this->orderFactory = $orderFactory;
    }

    public function updateStatus($orderIncrementId, $status)
    { 
        try {
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            if (!$order->getId()) {
                throw new LocalizedException(__('Order not found.'));
            }
            $availableStatuses = $order->getConfig()->getStateStatuses($order->getState());
            if (!in_array($status, $availableStatuses)) {
                throw new LocalizedException(__('Status transition not allowed.'));
            }

            $order->setStatus($status);
            $order->save();

            return ['success' => true, 'message' => 'Order status updated.'];
        } catch (\Exception $e) {
            throw new Exception(__($e->getMessage()), 0, Exception::HTTP_BAD_REQUEST);
        }
    }
}
