<?php
/**
 * Copyright © Baako Consulting LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BCMarketplace\QuoteMaintenance\Service;

use BCMarketplace\QuoteMaintenance\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Service for deleting old quotes
 *
 * @author Raphael Baako <rbaako@baakoconsultingllc.com>
 * @company Baako Consulting LLC
 */
class DeleteOldQuotes
{
    /**
     * @param Config $config
     * @param CollectionFactory $quoteCollectionFactory
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly CollectionFactory $quoteCollectionFactory,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute quote deletion
     *
     * @param bool $force Force execution even if disabled
     * @return int Number of quotes deleted
     * @throws LocalizedException
     */
    public function execute(bool $force = false): int
    {
        if (!$force && !$this->config->isEnabled()) {
            throw new LocalizedException(
                __(
                    'Quote maintenance is disabled. Enable it in Stores -> Configuration -> System -> Quote Maintenance Settings.'
                )
            );
        }

        $maxAgeInDays = $this->config->getMaxAgeInDays();
        $batchSize = $this->config->getBatchSize();
        $cutoffDate = (new \DateTimeImmutable())->modify("-{$maxAgeInDays} days");

        $this->logger->info(
            sprintf(
                'Starting quote maintenance: deleting quotes older than %d days (before %s)',
                $maxAgeInDays,
                $cutoffDate->format('Y-m-d H:i:s')
            )
        );

        $totalDeleted = 0;
        $currentPage = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $collection = $this->quoteCollectionFactory->create();
            $collection->addFieldToFilter('updated_at', ['lteq' => $cutoffDate->format('Y-m-d H:i:s')]);
            $collection->setPageSize($batchSize);
            $collection->setCurPage($currentPage);

            $pages = $collection->getLastPageNumber();
            $hasMorePages = $currentPage <= $pages;

            if (!$hasMorePages) {
                break;
            }

            $deletedInBatch = $this->deleteBatch($collection);
            $totalDeleted += $deletedInBatch;

            $this->logger->info(
                sprintf(
                    'Quote maintenance batch %d/%d: deleted %d quotes',
                    $currentPage,
                    $pages,
                    $deletedInBatch
                )
            );

            $currentPage++;
        }

        $this->logger->info(
            sprintf('Quote maintenance completed: deleted %d quotes total', $totalDeleted)
        );

        return $totalDeleted;
    }

    /**
     * Delete a batch of quotes
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $collection
     * @return int
     */
    private function deleteBatch(\Magento\Quote\Model\ResourceModel\Quote\Collection $collection): int
    {
        $deleted = 0;

        foreach ($collection as $quote) {
            try {
                $this->cartRepository->delete($quote);
                $deleted++;
            } catch (NoSuchEntityException $e) {
                // Quote already deleted, continue
                $this->logger->warning(
                    sprintf('Quote %d not found during deletion', $quote->getId())
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf(
                        'Error deleting quote %d: %s',
                        $quote->getId(),
                        $e->getMessage()
                    ),
                    ['exception' => $e]
                );
            }
        }

        return $deleted;
    }
}

