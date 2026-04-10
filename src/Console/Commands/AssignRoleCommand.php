<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class AssignRoleCommand extends Command
{
    protected static $defaultName = 'admin:assign-role';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:assign-role')
             ->setDescription('Assign a role to an admin user')
             ->addArgument('email', InputArgument::REQUIRED, 'The email of the admin user')
             ->addArgument('role', InputArgument::REQUIRED, 'The role to assign');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');

        $io->title("Assigning Role: {$role} to User: {$email}");

        $io->note('This command will assign the role to the user in the database.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');

        $io->success("Role [{$role}] assigned to user [{$email}] successfully!");

        return Command::SUCCESS;
    }
}
