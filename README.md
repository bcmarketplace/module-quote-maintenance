# BCMarketplace Quote Maintenance

## What It Does

This module automatically cleans up old shopping cart quotes from your database. By default, Magento keeps all abandoned carts forever, which can slow down your database over time. This module removes quotes older than a specified number of days.

## Installation

1. Install via Composer:
   ```bash
   composer require bcmarketplace/module-quote-maintenance
   ```

2. Enable the module:
   ```bash
   bin/magento module:enable BCMarketplace_QuoteMaintenance
   ```

3. Run setup upgrade:
   ```bash
   bin/magento setup:upgrade
   ```

4. Clear cache:
   ```bash
   bin/magento cache:flush
   ```

## Configuration

Navigate to: **Stores → Configuration → BCMarketplace → Quote Maintenance Settings**

### Settings

1. **Enable Quote Cleaning**: Turn the cleanup feature on or off
   - Default: Enabled

2. **Max Age (In Days)**: How old quotes must be before deletion
   - Range: 1-1095 days
   - Default: 730 days (2 years)

3. **Batch Size**: How many quotes to process at once
   - Range: 100-10,000
   - Default: 1,000
   - Lower numbers use less memory but take longer

## How It Works

### Automatic Cleanup

The module runs automatically every day at 2:00 AM via cron. It deletes quotes older than your configured "Max Age" setting.

### Manual Cleanup

You can also run cleanup manually:

```bash
bin/magento bcmarketplace:quotes:delete-old
```

To force cleanup even if disabled in settings:
```bash
bin/magento bcmarketplace:quotes:delete-old --force
```

## What Gets Deleted

The module deletes quotes based on their `updated_at` date. Only quotes older than your "Max Age" setting are removed. This includes:
- Abandoned carts
- Old guest quotes
- Any quote not updated within your time threshold

**Note**: The module only looks at the `updated_at` date, not order status or other factors.

## Logging

Cleanup activities are logged to: `var/log/system.log`

You'll see entries like:
- When cleanup starts
- How many quotes were deleted
- Any errors that occurred

## Troubleshooting

**Cron not running?**
- Check cron is enabled: `bin/magento cron:run`
- Verify cron schedule in admin: **System → Tools → Cron Schedule**

**No quotes being deleted?**
- Check module is enabled in configuration
- Verify quotes exist that are older than your "Max Age" setting
- Check system.log for error messages

**Performance issues?**
- Reduce the "Batch Size" setting
- Run cleanup during low-traffic periods
- Consider running manually during maintenance windows

## Support

For questions or support:
- **Email**: rbaako@baakoconsultingllc.com
- **Company**: Baako Consulting LLC

## License

Proprietary - All rights reserved.
