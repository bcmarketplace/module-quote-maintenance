<?php
/**
 * Copyright © Baako Consulting LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace BCMarketplace\QuoteMaintenance\Console\Command;

use BCMarketplace\QuoteMaintenance\Service\DeleteOldQuotes as DeleteOldQuotesService;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command to delete old quotes
 *
 * @author Raphael Baako <rbaako@baakoconsultingllc.com>
 * @company Baako Consulting LLC
 */
class DeleteOldQuotes extends Command
{
    private const OPTION_FORCE = 'force';

    /**
     * @param DeleteOldQuotesService $deleteOldQuotesService
     * @param string|null $name
     */
    public function __construct(
        private readonly DeleteOldQuotesService $deleteOldQuotesService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('bcmarketplace:quotes:delete-old');
        $this->setDescription('Delete quotes that haven\'t been updated in the configured number of days.');
        $this->addOption(
            self::OPTION_FORCE,
            'f',
            InputOption::VALUE_NONE,
            'Force execution even if disabled in configuration'
        );
        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = (bool) $input->getOption(self::OPTION_FORCE);
        $startTime = microtime(true);

        try {
            $deletedQuotes = $this->deleteOldQuotesService->execute($force);
            $runtime = round(microtime(true) - $startTime, 5);

            $output->writeln(
                sprintf(
                    '<info>Successfully deleted %d quote(s) in %s seconds.</info>',
                    $deletedQuotes,
                    $runtime
                )
            );

            return Command::SUCCESS;
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    '<error>An error occurred: %s</error>',
                    $e->getMessage()
                )
            );
            return Command::FAILURE;
        }
    }
}


