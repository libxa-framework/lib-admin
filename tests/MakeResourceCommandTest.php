<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Console\Commands\MakeResourceCommand;
use Libxa\Admin\Resources\AdminResource;
use PHPUnit\Framework\TestCase;

/**
 * What `admin:make-resource` writes to disk.
 *
 * The generated class fataled the moment it was autoloaded: it declared
 * `protected static string $model` while AdminResource declares `?string`,
 * and PHP requires a redeclared typed property to match exactly. The command
 * reported success, the file looked right, and the panel died on boot with a
 * message about property types that named neither the command nor the stub.
 *
 * It also appended "Resource" unconditionally, so `make-resource
 * SubscriberResource` produced SubscriberResourceResource, and the label and
 * plural derived from that name gave the panel a "Subscriber Resources"
 * section.
 */
final class MakeResourceCommandTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/libadmin-make-resource-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    /** Generate a resource without booting a whole application. */
    private function generate(string $name): string
    {
        $command = (new \ReflectionClass(MakeResourceCommand::class))->newInstanceWithoutConstructor();

        $method = new \ReflectionMethod($command, 'createResource');
        $method->setAccessible(true);

        $file = $this->directory . '/' . $name . 'Resource.php';
        $method->invoke($command, $file, $name, false);

        return $file;
    }

    public function test_the_generated_file_is_valid_php(): void
    {
        $file = $this->generate('Subscriber');

        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file), $output, $status);

        self::assertSame(0, $status, implode("\n", $output));
    }

    public function test_the_generated_property_types_match_the_base_class(): void
    {
        // The check that would have caught the fatal. Comparing against
        // AdminResource rather than a hardcoded string means the stub follows
        // the base class if it ever changes.
        $source = file_get_contents($this->generate('Subscriber'));

        foreach (['model', 'label', 'pluralLabel'] as $property) {
            $declared = (string) (new \ReflectionProperty(AdminResource::class, $property))->getType();

            $nullable = str_starts_with($declared, '?')
                ? substr($declared, 1) . '|null'
                : $declared;

            self::assertStringContainsString(
                'static ' . $nullable . ' $' . $property,
                $source,
                "\$$property must be declared as $nullable to match AdminResource.",
            );
        }
    }

    public function test_the_generated_class_can_actually_be_loaded(): void
    {
        // php -l parses; it does not link against the parent class, and the
        // property-type error only happens at link time.
        $file = $this->generate('Widget');

        $source = str_replace(
            ['namespace App\\Admin\\Resources;', '\\App\\Models\\Widget::class'],
            ['', 'null'],
            file_get_contents($file),
        );

        $class = 'GeneratedWidgetResource' . bin2hex(random_bytes(4));
        eval(str_replace('class WidgetResource', 'class ' . $class, substr($source, strlen('<?php'))));

        self::assertTrue(class_exists($class));
        self::assertInstanceOf(AdminResource::class, new $class());
    }
}
