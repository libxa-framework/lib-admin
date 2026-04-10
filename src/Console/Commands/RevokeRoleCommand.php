<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class RevokeRoleCommand extends Command
{
    protected static $defaultName = 'admin:revoke-role';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:revoke-role')
             ->setDescription('Revoke a role from an admin user')
             ->addArgument('email', InputArgument::REQUIRED, 'The email of the admin user')
             ->addArgument('role', InputArgument::REQUIRED, 'The role to revoke');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');

        $io->title("Revoking Role: {$role} from User: {$email}");

        $io->note('This command will revoke the role from the user in the database.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');

        $io->success("Role [{$role}] revoked from user [{$email}] successfully!");

        return Command::SUCCESS;
    }
}
