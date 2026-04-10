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

class MakeFieldCommand extends Command
{
    protected static $defaultName = 'admin:make-field';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-field')
             ->setDescription('Create a new admin field type')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the field');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        $path = $this->app->appPath('Admin/Fields');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}.php";

        if (file_exists($file)) {
            $io->error("Field [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Field: {$name}");

        $this->createField($file, $name);

        $io->success("Field [{$name}] created successfully at: {$file}");

        return Command::SUCCESS;
    }

    protected function createField(string $file, string $name): void
    {
        $slug = Str::kebab($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Fields;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Fields\\AdminField;';
        $lines[] = '';
        $lines[] = "class {$name} extends AdminField";
        $lines[] = '{';
        $lines[] = '    public function view(): string';
        $lines[] = '    {';
        $lines[] = '        return \'admin.fields.' . $slug . '\';';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function viewData(): array';
        $lines[] = '    {';
        $lines[] = '        return array_merge(parent::viewData(), [';
        $lines[] = '            // Add custom view data here';
        $lines[] = '        ]);';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function rules(): array';
        $lines[] = '    {';
        $lines[] = '        return [\'nullable\', \'string\'];';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
