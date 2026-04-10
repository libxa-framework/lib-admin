<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;

class RolesCommand extends Command
{
    protected static $defaultName = 'admin:roles';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:roles')
             ->setDescription('List all available roles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Available Roles');

        $roles = [
            'superadmin' => 'Super Administrator - Full access to everything',
            'editor' => 'Editor - Can manage content and media',
            'support' => 'Support Agent - Can manage orders and users',
            'viewer' => 'Read-only - Can view but not modify',
        ];

        foreach ($roles as $role => $description) {
            $io->text("<info>{$role}</info>: {$description}");
        }

        $io->success('Roles listed successfully!');

        return Command::SUCCESS;
    }
}
