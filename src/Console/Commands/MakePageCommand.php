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

class MakePageCommand extends Command
{
    protected static $defaultName = 'admin:make-page';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-page')
             ->setDescription('Create a new admin page')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the page');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        $path = $this->app->appPath('Admin/Pages');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}.php";

        if (file_exists($file)) {
            $io->error("Page [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Page: {$name}");

        $this->createPage($file, $name);

        $io->success("Page [{$name}] created successfully at: {$file}");

        return Command::SUCCESS;
    }

    protected function createPage(string $file, string $name): void
    {
        $slug = Str::kebab($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Pages;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Pages\\AdminPage;';
        $lines[] = '';
        $lines[] = "class {$name} extends AdminPage";
        $lines[] = '{';
        $lines[] = '    protected static string $path = \'' . $slug . '\';';
        $lines[] = '    protected static string $label = \'' . $name . '\';';
        $lines[] = '    protected static string $icon = \'document\';';
        $lines[] = '';
        $lines[] = '    public function viewData(): array';
        $lines[] = '    {';
        $lines[] = '        return [];';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function view(): string';
        $lines[] = '    {';
        $lines[] = '        return \'admin.pages.' . $slug . '\';';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
