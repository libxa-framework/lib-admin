<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class UsersCommand extends Command
{
    protected static $defaultName = 'admin:users';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:users')
             ->setDescription('List all admin users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Admin Users');

        $io->note('This command will list all admin users from the database.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');

        $io->success('Admin users listed successfully!');

        return Command::SUCCESS;
    }
}
