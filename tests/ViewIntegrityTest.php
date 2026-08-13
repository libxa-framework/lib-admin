<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Things about the shipped views that are easy to get wrong and silent when
 * wrong.
 *
 * Every write form in the panel shipped without a CSRF token: login, create,
 * edit, delete and logout. With CSRF middleware on — the default — that meant
 * the panel could not be signed into, and nothing could be saved. The failure
 * is a 419 with no clue as to which form is missing what, so it is worth
 * checking mechanically.
 */
final class ViewIntegrityTest extends TestCase
{
    private const VIEWS = __DIR__ . '/../src/Resources/views';

    /**
     * @return array<string, array{string}>
     */
    public static function viewFiles(): array
    {
        $cases = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::VIEWS, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $cases[basename($file->getPathname())] = [$file->getPathname()];
            }
        }

        return $cases;
    }

    #[DataProvider('viewFiles')]
    public function test_every_form_carries_a_csrf_token(string $path): void
    {
        $source = file_get_contents($path);

        $forms = substr_count($source, '<form');

        if ($forms === 0) {
            $this->expectNotToPerformAssertions();

            return;
        }

        self::assertSame(
            $forms,
            substr_count($source, '@csrf'),
            basename($path) . ' has ' . $forms . ' form(s) but ' . substr_count($source, '@csrf') . ' @csrf token(s).',
        );
    }

    #[DataProvider('viewFiles')]
    public function test_no_view_references_a_dotted_admin_namespace(string $path): void
    {
        // `admin.columns.text` does not resolve; `admin::columns.text` does.
        // The two look near-identical in a diff.
        $source = file_get_contents($path);

        self::assertDoesNotMatchRegularExpression(
            '/[\'"]admin\.(columns|layouts|resources|partials|widgets|fields)\./',
            $source,
            basename($path) . ' uses a dotted admin namespace; views are registered under "admin::".',
        );
    }
}
