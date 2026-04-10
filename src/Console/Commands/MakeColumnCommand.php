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

class MakeColumnCommand extends Command
{
    protected static $defaultName = 'admin:make-column';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-column')
             ->setDescription('Create a new admin column type')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the column');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        $path = $this->app->appPath('Admin/Columns');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}.php";

        if (file_exists($file)) {
            $io->error("Column [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Column: {$name}");

        $this->createColumn($file, $name);

        $io->success("Column [{$name}] created successfully at: {$file}");

        return Command::SUCCESS;
    }

    protected function createColumn(string $file, string $name): void
    {
        $slug = Str::kebab($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Columns;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Columns\\AdminColumn;';
        $lines[] = '';
        $lines[] = "class {$name} extends AdminColumn";
        $lines[] = '{';
        $lines[] = '    public function view(): string';
        $lines[] = '    {';
        $lines[] = '        return \'admin.columns.' . $slug . '\';';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
