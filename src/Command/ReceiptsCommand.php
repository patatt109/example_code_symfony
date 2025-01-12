<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\ReceiptsBankOne;
use App\Services\ReceiptsBankTwo;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:receipts')]
class ReceiptsCommand extends Command
{
    public function __construct(
        private readonly ReceiptsBankOne $receiptsBankOne,
        private readonly ReceiptsBankTwo $receiptsBankTwo,
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info("Receipts command started");
        $output->writeln("Receipts command started");

        try {
            $this->logger->info("receiptsBankOne->load started");
            $output->writeln("receiptsBankOne->load started");
            $this->receiptsBankOne->load();
            $this->logger->info("receiptsBankOne->load finished");
            $output->writeln("receiptsBankOne->load finished");

            $this->logger->info("receiptsBankOne->publish started");
            $output->writeln("receiptsBankOne->publish started");
            $this->receiptsBankOne->publish();
            $this->logger->info("receiptsBankOne->publish finished");
            $output->writeln("receiptsBankOne->publish finished");
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        try {
            $this->logger->info("receiptsBankTwo->load started");
            $output->writeln("receiptsBankTwo->load started");
            $this->receiptsBankTwo->load();
            $this->logger->info("receiptsBankTwo->load finished");
            $output->writeln("receiptsBankTwo->load finished");

            $this->logger->info("receiptsBankTwo->publish started");
            $output->writeln("receiptsBankTwo->publish started");
            $this->receiptsBankTwo->publish();
            $this->logger->info("receiptsBankTwo->publish finished");
            $output->writeln("receiptsBankTwo->publish finished");
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        $this->logger->info("Receipts command finished");
        $output->writeln("Receipts command finished");
        return Command::SUCCESS;
    }
}