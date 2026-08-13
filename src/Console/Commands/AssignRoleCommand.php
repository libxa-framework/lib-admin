<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Libxa\Admin\Authorization\Roles;
use Libxa\Admin\Models\AdminUser;
use Libxa\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Give an admin a role.
 *
 * This printed "Role [x] assigned to user [y] successfully!" and wrote
 * nothing. Someone granting an account access, seeing that, and moving on
 * would have left it with none.
 */
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

        $email = (string) $input->getArgument('email');
        $roleName = (string) $input->getArgument('role');

        try {
            $user = AdminUser::findByEmail($email);
        } catch (Throwable $e) {
            $io->error('Could not read the admin_users table. Have you run migrations? (' . $e->getMessage() . ')');

            return Command::FAILURE;
        }

        if ($user === null) {
            $io->error("No admin user with the email [$email].");

            return Command::FAILURE;
        }

        $role = Roles::find($roleName);

        if ($role === null) {
            $io->error("No role named [$roleName].");
            $io->text('Run php libxa admin:roles to see what exists, or admin:sync-permissions to create the defaults.');

            return Command::FAILURE;
        }

        $attached = Roles::attach((int) $role->id, (int) $user->getAuthIdentifier());

        if (! $attached) {
            $io->note("[$email] already has the role [$roleName]. Nothing changed.");

            return Command::SUCCESS;
        }

        $io->success("[$email] now has the role [$roleName].");

        return Command::SUCCESS;
    }
}
