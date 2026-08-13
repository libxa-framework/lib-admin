<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Libxa\Admin\Authorization\Gate;
use Libxa\Admin\Authorization\Roles;
use Libxa\Atlas\DB;
use Libxa\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * List the roles that exist.
 *
 * This printed a hardcoded list of four role names with descriptions,
 * regardless of what was in the database — which was nothing, because nothing
 * ever created a role. It read as confirmation that roles were set up.
 */
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
             ->setDescription('List all available roles')
             ->addOption('permissions', 'p', InputOption::VALUE_NONE, 'Show the permissions each role grants');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Roles');

        try {
            $roles = DB::table('roles')->orderBy('id')->get();
        } catch (Throwable $e) {
            $io->error('Could not read the roles table. Have you run migrations? (' . $e->getMessage() . ')');

            return Command::FAILURE;
        }

        if ($roles === []) {
            $io->warning('No roles exist yet.');
            $io->text('Run php libxa admin:sync-permissions to create the defaults and grant their permissions.');

            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($roles as $role) {
            $rows[] = [
                $role->name,
                $role->label,
                (string) Roles::holderCount((int) $role->id),
                $role->name === Gate::SUPERADMIN
                    ? 'everything (by name)'
                    : (string) $this->permissionCount((int) $role->id),
            ];
        }

        $io->table(['Name', 'Label', 'Admins', 'Permissions'], $rows);

        if ($input->getOption('permissions')) {
            foreach ($roles as $role) {
                if ($role->name === Gate::SUPERADMIN) {
                    continue;
                }

                $names = $this->permissionNames((int) $role->id);

                $io->section((string) $role->name);
                $io->text($names === [] ? 'None.' : implode(', ', $names));
            }
        }

        return Command::SUCCESS;
    }

    private function permissionCount(int $roleId): int
    {
        return DB::table('permission_role')->where('role_id', $roleId)->count();
    }

    /** @return list<string> */
    private function permissionNames(int $roleId): array
    {
        $rows = DB::select(
            'SELECT p.name
             FROM permissions p
             INNER JOIN permission_role pr ON pr.permission_id = p.id
             WHERE pr.role_id = ?
             ORDER BY p.name',
            [$roleId],
        );

        return array_map(static fn (array $row): string => (string) $row['name'], $rows);
    }
}
