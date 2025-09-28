<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ExchangeRateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-exchange-rates',
    description: 'Update cryptocurrency exchange rates from Binance API',
)]
class UpdateExchangeRatesCommand extends Command
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(
            'This command fetches the latest cryptocurrency exchange rates from Binance API and stores them in the database.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Crypto Exchange Rates Update');

        try {
            $io->section('Fetching exchange rates from Binance API...');
            $this->exchangeRateService->updateExchangeRates();
            $io->success('Exchange rates updated successfully');

            $io->writeln(sprintf('<info>Command completed at: %s</info>', date('Y-m-d H:i:s')));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update exchange rates: '.$e->getMessage());

            if ($output->isVerbose()) {
                $io->writeln('<error>Stack trace:</error>');
                $io->writeln($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
