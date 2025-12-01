<?php
/**
 * Copyright © Baako Consulting LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BCMarketplace\QuoteMaintenance\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration model for BCMarketplace QuoteMaintenance module
 *
 * Provides methods to read quote maintenance configuration values.
 *
 * @author Raphael Baako <rbaako@baakoconsultingllc.com>
 * @company Baako Consulting LLC
 */
class Config
{
    private const XML_PATH_ENABLED = 'bcmarketplace/quote_maintenance/enabled';
    private const XML_PATH_MAX_AGE_DAYS = 'bcmarketplace/quote_maintenance/max_age_days';
    private const XML_PATH_BATCH_SIZE = 'bcmarketplace/quote_maintenance/batch_size';

    private const DEFAULT_MAX_AGE_DAYS = 730;
    private const DEFAULT_BATCH_SIZE = 1000;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if quote maintenance is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get maximum age in days for quotes to be deleted
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxAgeInDays(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_MAX_AGE_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : self::DEFAULT_MAX_AGE_DAYS;
    }

    /**
     * Get batch size for processing quotes
     *
     * @param int|null $storeId
     * @return int
     */
    public function getBatchSize(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_BATCH_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value > 0 ? $value : self::DEFAULT_BATCH_SIZE;
    }
}

