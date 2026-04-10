<?php

declare(strict_types=1);

namespace Libxa\Admin\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Libxa\Foundation\Application;
use Libxa\Support\Str;

class MakeActionCommand extends Command
{
    protected static $defaultName = 'admin:make-action';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-action')
             ->setDescription('Create a new admin action')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the action');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        $path = $this->app->appPath('Admin/Actions');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}.php";

        if (file_exists($file)) {
            $io->error("Action [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Action: {$name}");

        $this->createAction($file, $name);

        $io->success("Action [{$name}] created successfully at: {$file}");

        return Command::SUCCESS;
    }

    protected function createAction(string $file, string $name): void
    {
        $slug = Str::kebab($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Actions;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Actions\\Action;';
        $lines[] = '';
        $lines[] = "class {$name} extends Action";
        $lines[] = '{';
        $lines[] = '    public function handle($record, array $data = []): void';
        $lines[] = '    {';
        $lines[] = '        // Add your action logic here';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
