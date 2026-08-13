<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Libxa\Admin\Authorization\Gate;
use Libxa\Admin\Authorization\Roles;
use Libxa\Admin\Models\AdminUser;
use Libxa\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Take a role away from an admin.
 *
 * Like its counterpart, this reported success and wrote nothing — so somebody
 * revoking access from a departing colleague, and seeing it confirmed, left
 * that account with everything it had.
 */
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
             ->addArgument('role', InputArgument::REQUIRED, 'The role to revoke')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Revoke the last superadmin anyway');
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

            return Command::FAILURE;
        }

        // Removing the last superadmin leaves a panel nobody can grant
        // anything in, and the only way back is the database. Worth a
        // deliberate --force rather than a confirmation prompt, which a
        // script would not see.
        if ($roleName === Gate::SUPERADMIN
            && Roles::holderCount((int) $role->id) <= 1
            && ! $input->getOption('force')
        ) {
            $io->error('That is the last superadmin. Removing it leaves nobody who can grant roles, and no way back through the panel.');
            $io->text('Assign superadmin to another account first, or pass --force if you mean it.');

            return Command::FAILURE;
        }

        $detached = Roles::detach((int) $role->id, (int) $user->getAuthIdentifier());

        if (! $detached) {
            $io->note("[$email] does not have the role [$roleName]. Nothing changed.");

            return Command::SUCCESS;
        }

        $io->success("[$email] no longer has the role [$roleName].");

        return Command::SUCCESS;
    }
}
