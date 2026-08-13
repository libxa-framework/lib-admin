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

class MakeResourceCommand extends Command
{
    protected static $defaultName = 'admin:make-resource';

    public function __construct(protected Application $app)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('admin:make-resource')
             ->setDescription('Create a new admin resource class')
             ->addArgument('name', InputArgument::REQUIRED, 'The name of the resource')
             ->addOption('from-schema', 's', InputOption::VALUE_NONE, 'Infer columns and fields from live database schema')
             ->addOption('soft-deletes', 'd', InputOption::VALUE_NONE, 'Enable soft deletes support');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = Str::studly($input->getArgument('name'));

        // "Subscriber" and "SubscriberResource" mean the same thing, and
        // appending unconditionally produced SubscriberResourceResource. The
        // label and the plural are derived from this name too, so the panel
        // ended up with a "Subscriber Resources" section.
        if (str_ends_with($name, 'Resource')) {
            $name = substr($name, 0, -strlen('Resource'));
        }

        if ($name === '') {
            $io->error('A resource needs a name of its own, not just "Resource".');

            return Command::FAILURE;
        }
        $fromSchema = $input->getOption('from-schema');
        $softDeletes = $input->getOption('soft-deletes');

        $path = $this->app->appPath('Admin/Resources');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . DIRECTORY_SEPARATOR . "{$name}Resource.php";

        if (file_exists($file)) {
            $io->error("Resource [{$name}] already exists!");
            return Command::FAILURE;
        }

        $io->title("Creating Admin Resource: {$name}");

        $this->createResource($file, $name, $softDeletes);

        $io->success("Resource [{$name}] created successfully at: {$file}");

        if ($fromSchema) {
            $io->note('Schema inference is not yet implemented. Please add columns and fields manually.');
        }

        return Command::SUCCESS;
    }

    protected function createResource(string $file, string $name, bool $softDeletes): void
    {
        $plural = Str::plural($name);
        $snake = Str::snake($name);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace App\\Admin\\Resources;';
        $lines[] = '';
        $lines[] = 'use Libxa\\Admin\\Resources\\AdminResource;';
        $lines[] = 'use Libxa\\Admin\\Fields\\TextInput;';
        $lines[] = 'use Libxa\\Admin\\Columns\\TextColumn;';
        $lines[] = '';
        $lines[] = "class {$name}Resource extends AdminResource";
        $lines[] = '{';
        // These three are nullable on AdminResource, and PHP requires a
        // redeclared typed property to match exactly. Emitting `string` here
        // made every generated resource fatal the moment it was autoloaded:
        // "Type of ...::$model must be ?string (as in class AdminResource)".
        $lines[] = '    protected static string|null $model = \App\Models\\' . $name . '::class;';
        $lines[] = '    protected static string|null $label = \'' . $name . '\';';
        $lines[] = '    protected static string|null $pluralLabel = \'' . $plural . '\';';
        $lines[] = '    protected static string $icon = \'folder\';';
        $lines[] = '    protected static string $group = \'General\';';
        $lines[] = '    protected static string $defaultSort = \'created_at\';';
        $lines[] = '    protected static string $defaultOrder = \'desc\';';
        $lines[] = '    protected static int $perPage = 25;';
        $lines[] = '';
        if ($softDeletes) {
            $lines[] = '    protected static bool $softDeletes = true;';
            $lines[] = '';
        }
        $lines[] = '    public function columns(): array';
        $lines[] = '    {';
        $lines[] = '        return [';
        $lines[] = '            TextColumn::make(\'id\')->sortable(),';
        $lines[] = '            TextColumn::make(\'name\')->sortable()->searchable(),';
        $lines[] = '        ];';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function fields(): array';
        $lines[] = '    {';
        $lines[] = '        return [';
        $lines[] = '            TextInput::make(\'name\')->required(),';
        $lines[] = '        ];';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function filters(): array';
        $lines[] = '    {';
        $lines[] = '        return [];';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function actions(): array';
        $lines[] = '    {';
        $lines[] = '        return [];';
        $lines[] = '    }';
        $lines[] = '}';

        file_put_contents($file, implode("\n", $lines) . "\n");
    }
}
