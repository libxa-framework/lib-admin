<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Libxa\Admin\Authorization\Gate;
use Libxa\Admin\Authorization\Permission;
use Libxa\Admin\Authorization\Roles;
use Libxa\Admin\Facades\Admin;
use Libxa\Atlas\DB;
use Libxa\Foundation\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Write the permissions the registered resources imply into the database.
 *
 * This command reported "Permissions synced successfully!" and did nothing,
 * which meant the permissions table was empty on every installation, which
 * meant every role granted nothing — and since nothing checked permissions
 * either, none of that was visible.
 */
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
             ->setDescription('Sync permissions from resource definitions to database')
             ->addOption('prune', null, InputOption::VALUE_NONE, 'Delete permissions no longer defined by any resource');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Syncing permissions');

        $seeded = Roles::seed();

        if ($seeded !== []) {
            $io->text('Created roles: ' . implode(', ', $seeded));
        }

        $defined = $this->defined();

        if ($defined === []) {
            $io->warning('No resources are registered, so there is nothing to sync beyond the panel permissions.');
        }

        $existing = $this->existingByName();

        $created = 0;

        foreach ($defined as $name => $meta) {
            if (isset($existing[$name])) {
                continue;
            }

            DB::table('permissions')->insert([
                'name' => $name,
                'label' => $meta['label'],
                'resource' => $meta['resource'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $created++;
        }

        $pruned = 0;

        if ($input->getOption('prune')) {
            foreach ($existing as $name => $id) {
                if (isset($defined[$name])) {
                    continue;
                }

                // The join rows go too, or the permission_role table keeps
                // pointing at an id that no longer exists.
                DB::table('permission_role')->where('permission_id', $id)->delete();
                DB::table('permissions')->where('id', $id)->delete();

                $pruned++;
            }
        }

        $granted = $this->grantDefaults($defined);

        $io->success(sprintf(
            '%d permission(s) created, %d granted to the default roles%s.',
            $created,
            $granted,
            $pruned > 0 ? ", {$pruned} pruned" : '',
        ));

        if (! $input->getOption('prune')) {
            $io->note('Permissions for resources you have removed are left in place. Pass --prune to delete them.');
        }

        $io->note('superadmin is not granted permissions here: it is allowed everything by name, so a resource added later is covered without another sync.');

        $this->warnAboutOrphanedAdmins($io);

        return Command::SUCCESS;
    }

    /**
     * Say so when nobody can administer the panel.
     *
     * Accounts that existed before permissions were enforced hold no roles,
     * so after upgrading they can sign in and do nothing. Deliberately not
     * fixed automatically: granting superadmin to whichever account happens
     * to be first is a privilege escalation performed by a migration, and the
     * one thing worse than an admin who cannot do anything is one who
     * silently can do everything.
     */
    private function warnAboutOrphanedAdmins(SymfonyStyle $io): void
    {
        $superadmin = Roles::find(Gate::SUPERADMIN);

        if ($superadmin !== null && Roles::holderCount((int) $superadmin->id) > 0) {
            return;
        }

        // Keyed on role_id, not id: role_user is a pure join table and has no
        // surrogate key.
        $orphans = DB::select(
            'SELECT u.email
             FROM admin_users u
             LEFT JOIN role_user ru ON ru.admin_user_id = u.id
             WHERE ru.role_id IS NULL
             ORDER BY u.id
             LIMIT 5',
        );

        if ($orphans === []) {
            return;
        }

        $io->warning('No account holds superadmin, so nobody can administer this panel.');

        $io->text('These accounts have no role at all, and can sign in but do nothing:');

        foreach ($orphans as $row) {
            $io->text('  ' . $row['email']);
        }

        $io->text('');
        $io->text('Grant one of them:');
        $io->text('  php libxa admin:assign-role ' . ($orphans[0]['email'] ?? 'you@example.com') . ' superadmin');
    }

    /**
     * Every permission the panel and its registered resources define.
     *
     * @return array<string, array{label: string, resource: string|null}>
     */
    private function defined(): array
    {
        $defined = [];

        foreach (Permission::panel() as $name => $label) {
            $defined[$name] = ['label' => $label, 'resource' => null];
        }

        foreach (Admin::getResources() as $class) {
            if (! $class::isAuthorized()) {
                continue;
            }

            $prefix = $class::permissionPrefix();

            foreach ($class::permissions() as $ability) {
                $name = $prefix . '.' . $ability;

                $defined[$name] = [
                    'label' => Permission::label($name),
                    'resource' => $prefix,
                ];
            }
        }

        return $defined;
    }

    /** @return array<string, int> name => id */
    private function existingByName(): array
    {
        $map = [];

        foreach (DB::table('permissions')->get() as $row) {
            $map[(string) $row->name] = (int) $row->id;
        }

        return $map;
    }

    /**
     * Give the default roles the permissions they are defined as having.
     *
     * Only adds. A permission someone has revoked by hand stays revoked:
     * re-running a sync must not quietly restore access that was taken away
     * on purpose.
     *
     * @param array<string, array{label: string, resource: string|null}> $defined
     */
    private function grantDefaults(array $defined): int
    {
        $permissionIds = $this->existingByName();
        $granted = 0;

        $abilitiesByRole = Roles::resourceAbilities();
        $panelByRole = Roles::panelPermissions();

        foreach (array_keys(Roles::defaults()) as $roleName) {
            $role = Roles::find($roleName);

            if ($role === null) {
                continue;
            }

            $wanted = [];

            foreach ($panelByRole[$roleName] ?? [] as $name) {
                $wanted[] = $name;
            }

            foreach ($defined as $name => $meta) {
                if ($meta['resource'] === null) {
                    continue;
                }

                $ability = substr($name, strlen($meta['resource']) + 1);

                if (in_array($ability, $abilitiesByRole[$roleName] ?? [], true)) {
                    $wanted[] = $name;
                }
            }

            foreach (array_unique($wanted) as $name) {
                $permissionId = $permissionIds[$name] ?? null;

                if ($permissionId === null) {
                    continue;
                }

                $already = DB::table('permission_role')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $role->id)
                    ->first();

                if ($already !== null) {
                    continue;
                }

                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ]);

                $granted++;
            }
        }

        return $granted;
    }
}
