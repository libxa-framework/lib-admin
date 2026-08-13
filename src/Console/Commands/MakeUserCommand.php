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

    /** Short enough to type, long enough that the panel is not trivially guessable. */
    private const MIN_PASSWORD_LENGTH = 12;

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

        $interactive = $input->isInteractive();

        // Get user input
        $name = $input->getArgument('name') ?: ($interactive ? $io->ask('Enter the admin name', 'Admin') : 'Admin');
        $email = $input->getArgument('email') ?: ($interactive ? $io->ask('Enter the admin email', 'admin@example.com') : null);

        if (! is_string($email) || $email === '') {
            $io->error('An email is required. Pass it as an argument when running non-interactively.');

            return Command::FAILURE;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $io->error("[$email] is not a valid email address.");

            return Command::FAILURE;
        }

        // The password argument was declared and then ignored: this always
        // prompted, so the documented non-interactive form hung, and with
        // --no-interaction askHidden() returned null and password_hash() threw
        // a type error instead of saying what was missing.
        $password = $input->getArgument('password');

        if ($password === null) {
            if (! $interactive) {
                $io->error('A password is required. Pass it as the third argument when running non-interactively.');

                return Command::FAILURE;
            }

            $password = $io->askHidden('Enter the admin password');

            if ($password !== $io->askHidden('Confirm the password')) {
                $io->error('Passwords do not match!');

                return Command::FAILURE;
            }
        }

        if (! is_string($password) || strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $io->error('The password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.');

            return Command::FAILURE;
        }

        // Checked before insert so a second run reports the real problem
        // rather than a unique-constraint violation from the driver.
        if (\Libxa\Admin\Models\AdminUser::findByEmail($email) !== null) {
            $io->error("An admin user with the email [$email] already exists.");

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

        $io->note('Make sure you have run migrations first: php libxa migrate');

        if ($interactive && ! $io->confirm('Continue?', true)) {
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

            $attached = $this->attachRole($user, $role);

            $io->success("Admin user [$email] created successfully!");

            // Said "with role [x]" unconditionally before, including when
            // nothing was written to role_user. A panel that reports a
            // permission it did not grant is worse than one that grants none.
            if ($attached) {
                $io->text(" Role: $role");
            } else {
                $io->warning("The role [$role] does not exist, so none was assigned. Create it in the roles table and attach it, or the user will have no permissions.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Failed to create user: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Attach a named role, if that role exists.
     *
     * Returns false when it does not, rather than creating one: roles carry
     * permissions, and inventing a "superadmin" with an empty permission set
     * would silently produce an account that cannot do anything.
     */
    private function attachRole(\Libxa\Admin\Models\AdminUser $user, string $role): bool
    {
        $row = \Libxa\Atlas\DB::table('roles')->where('name', $role)->first();

        if ($row === null) {
            return false;
        }

        \Libxa\Atlas\DB::table('role_user')->insert([
            'role_id' => $row->id,
            'admin_user_id' => $user->getAuthIdentifier(),
        ]);

        return true;
    }
}
