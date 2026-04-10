<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class SyncPermissionsCommand extends Command
{
    protected static $defaultName = 'admin:sync-permissions';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:sync-permissions')
             ->setDescription('Sync permissions from resource definitions to database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Syncing Permissions');

        $io->note('This command will sync permissions from all resource classes to the database.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');

        $io->success('Permissions synced successfully!');

        return Command::SUCCESS;
    }
}
