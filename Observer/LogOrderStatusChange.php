<?php

namespace Vendor\CustomOrderProcessing\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class LogOrderStatusChange implements ObserverInterface
{
    protected $resource;
    protected $logger;
    protected $transportBuilder;
    protected $storeManager;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        if ($oldStatus !== $newStatus) {
            try {
                $connection = $this->resource->getConnection();
                $table = $connection->getTableName('custom_order_status_log');

                $connection->insert($table, [
                    'order_id' => $order->getEntityId(),
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
                ]);

                if ($newStatus === 'shipped') {
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier('order_shipped_email_template')
                        ->setTemplateOptions([
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getId(),
                        ])
                        ->setTemplateVars([
                            'order' => $order,
                        ])
                        ->setFromByScope('general')
                        ->addTo($order->getCustomerEmail())
                        ->getTransport();

                    $transport->sendMessage();
                }

            } catch (\Exception $e) {
                $this->logger->error('Order Status Observer Error: ' . $e->getMessage());
            }
        }
    }
}
