<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class SeedDemoCommand extends Command
{
    protected static $defaultName = 'admin:seed-demo';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:seed-demo')
             ->setDescription('Seed the admin panel with demo data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Seeding Demo Data');

        $io->note('This command will seed the admin panel with demo data.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');
        $io->warning('This will create demo data in your database.');

        if (! $io->confirm('Do you want to continue?', false)) {
            $io->note('Seeding cancelled.');
            return Command::SUCCESS;
        }

        $io->success('Demo data seeded successfully!');

        return Command::SUCCESS;
    }
}
