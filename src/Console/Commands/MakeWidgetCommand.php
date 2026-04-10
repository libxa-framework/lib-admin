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

class MakeWidgetCommand extends Command
{
    protected static $defaultName = 'admin:make-widget';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-widget')
             ->setDescription('Create a new admin widget')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the widget');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        $path = $this->app->appPath('Admin/Widgets');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}.php";

        if (file_exists($file)) {
            $io->error("Widget [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Widget: {$name}");

        $this->createWidget($file, $name);

        $io->success("Widget [{$name}] created successfully at: {$file}");

        return Command::SUCCESS;
    }

    protected function createWidget(string $file, string $name): void
    {
        $slug = Str::kebab($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Widgets;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Widgets\\AdminWidget;';
        $lines[] = '';
        $lines[] = "class {$name} extends AdminWidget";
        $lines[] = '{';
        $lines[] = '    public function data(): array';
        $lines[] = '    {';
        $lines[] = '        return [];';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function view(): string';
        $lines[] = '    {';
        $lines[] = '        return \'admin.widgets.' . $slug . '\';';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function span(): int';
        $lines[] = '    {';
        $lines[] = '        return 1;';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
