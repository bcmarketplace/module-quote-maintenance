<?php
/**
 * Copyright © Baako Consulting LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BCMarketplace\QuoteMaintenance\Cron;

use BCMarketplace\QuoteMaintenance\Service\DeleteOldQuotes as DeleteOldQuotesService;
use Magento\Cron\Model\Schedule;
use Psr\Log\LoggerInterface;

/**
 * Cron job for deleting old quotes
 *
 * @author Raphael Baako <rbaako@baakoconsultingllc.com>
 * @company Baako Consulting LLC
 */
class DeleteOldQuotes
{
    /**
     * @param DeleteOldQuotesService $deleteOldQuotesService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DeleteOldQuotesService $deleteOldQuotesService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute cron job
     *
     * @param Schedule $schedule
     * @return void
     */
    public function execute(Schedule $schedule): void
    {
        $startTime = microtime(true);

        try {
            $deletedQuotes = $this->deleteOldQuotesService->execute();
            $runtime = round(microtime(true) - $startTime, 5);

            $message = sprintf(
                'Deleted %d quote(s) in %s seconds.',
                $deletedQuotes,
                $runtime
            );

            $schedule->setMessages($message);
            $this->logger->info($message);
        } catch (\Exception $e) {
            $errorMessage = sprintf(
                'Error during quote maintenance: %s',
                $e->getMessage()
            );

            $schedule->setMessages($errorMessage);
            $this->logger->error($errorMessage, ['exception' => $e]);
        }
    }
}


