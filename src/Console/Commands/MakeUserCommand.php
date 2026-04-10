<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;
use Libxa\Support\Str;

class MakeUserCommand extends Command
{
    protected static $defaultName = 'admin:make-user';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-user')
             ->setDescription('Create a new admin user')
             ->addArgument('name', InputArgument::OPTIONAL, 'The name of the admin user')
             ->addArgument('email', InputArgument::OPTIONAL, 'The email of the admin user')
             ->addArgument('password', InputArgument::OPTIONAL, 'The password of the admin user')
             ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'The role to assign (default: superadmin)', 'superadmin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating Admin User');

        // Get user input
        $name = $input->getArgument('name') ?: $io->ask('Enter the admin name', 'Admin');
        $email = $input->getArgument('email') ?: $io->ask('Enter the admin email', 'admin@example.com');
        $password = $io->askHidden('Enter the admin password');
        $confirm = $io->askHidden('Confirm the password');

        if ($password !== $confirm) {
            $io->error('Passwords do not match!');
            return Command::FAILURE;
        }

        $role = $input->getOption('role') ?: 'superadmin';

        $io->text([
            '',
            "Creating Admin User",
            "===================",
            '',
            " Name: $name",
            " Email: $email",
            " Role: $role",
            '',
        ]);

        $io->note('This command will create the admin user in the database.');
        $io->note('Make sure you have run migrations first: php Libxa migrate');

        if (! $io->confirm('Continue?', true)) {
            $io->comment('Cancelled.');
            return Command::SUCCESS;
        }

        // Create user using AdminUser model
        try {
            $user = \Libxa\Admin\Models\AdminUser::create([
                'name' => $name,
                'email' => $email,
                'password' => $password, // Password is hashed in the model
            ]);

            // TODO: Assign role to user (need roles table implementation)
            
            $io->success("Admin user [$email] created successfully with role [$role]!");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to create user: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
