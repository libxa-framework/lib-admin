<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Media\MediaStore;
use PHPUnit\Framework\TestCase;

/**
 * The upload rules.
 *
 * File upload is where an admin panel turns into remote code execution, so
 * these pin the parts that decide whether something is accepted. The store
 * writes to the database on success, so the tests here cover everything up to
 * that point: the refusals, which is where the security lives.
 */
final class MediaStoreTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/libadmin-media-' . bin2hex(random_bytes(4));
        @mkdir($this->tmp, 0o755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmp . '/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->tmp);
    }

    /** A file on disk with the given bytes, wrapped as an upload. */
    private function upload(string $name, string $contents): \Libxa\Http\UploadedFile
    {
        $path = $this->tmp . '/' . bin2hex(random_bytes(6));
        file_put_contents($path, $contents);

        return new \Libxa\Http\UploadedFile(
            $name,
            // Deliberately a lie in most of these tests: this is the value the
            // client controls, and nothing may depend on it.
            'image/png',
            $path,
            UPLOAD_ERR_OK,
            strlen($contents),
        );
    }

    private function store(int $maxBytes = 10_485_760): MediaStore
    {
        $storage = new class () {
            public array $written = [];

            public function put(string $path, string $contents): bool
            {
                $this->written[$path] = $contents;

                return true;
            }

            public function delete(string $path): bool
            {
                unset($this->written[$path]);

                return true;
            }
        };

        return new MediaStore($storage, 'public', 'media', $maxBytes);
    }

    private const PNG = "\x89PNG\r\n\x1A\n" . 'rest of a png';

    private function expectRefusal(string $pattern): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches($pattern);
    }

    // ── the attacks ──────────────────────────────────────────────────────

    public function test_a_php_file_declared_as_an_image_is_refused(): void
    {
        // The whole point. `$_FILES['type']` says image/png because the client
        // said so; the extension and the bytes are what get checked.
        $this->expectRefusal('/not accepted/');

        $this->store()->store($this->upload('shell.php', '<?php system($_GET["c"]); ?>'));
    }

    public function test_the_php_variants_a_deny_list_would_miss_are_refused(): void
    {
        // Why the list is an allow-list. Every one of these has been a working
        // handler mapping somewhere.
        foreach (['x.php5', 'x.phtml', 'x.phar', 'x.PHP', 'x.php.png.php', 'x.inc'] as $name) {
            try {
                $this->store()->store($this->upload($name, '<?php echo 1;'));
                self::fail("[{$name}] should have been refused");
            } catch (\RuntimeException) {
                self::assertTrue(true);
            }
        }
    }

    public function test_a_png_extension_over_php_contents_is_refused(): void
    {
        // The extension is on the list, so only inspecting the bytes catches
        // this. A server misconfigured to run .png through PHP would execute it.
        $this->expectRefusal('/contents are/');

        $this->store()->store($this->upload('avatar.png', '<?php system("id"); ?>'));
    }

    public function test_an_svg_is_refused(): void
    {
        // SVG is XML, it can carry script, and the browser runs it in the
        // origin that served it. An SVG upload is stored XSS on the admin
        // domain.
        $this->expectRefusal('/not accepted/');

        $this->store()->store($this->upload(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        ));
    }

    public function test_a_file_with_no_extension_is_refused(): void
    {
        $this->expectRefusal('/not accepted/');

        $this->store()->store($this->upload('passwd', 'root:x:0:0'));
    }

    public function test_a_traversing_filename_cannot_choose_where_the_file_lands(): void
    {
        // It is refused for its extension, but the property that matters is
        // that the client's name is never used as a path at all.
        $this->expectRefusal('/not accepted/');

        $this->store()->store($this->upload('../../../etc/cron.d/evil', 'x'));
    }

    // ── limits ───────────────────────────────────────────────────────────

    public function test_a_file_over_the_limit_is_refused_with_both_numbers(): void
    {
        $this->expectRefusal('/limit is/');

        $this->store(maxBytes: 100)->store($this->upload('big.png', self::PNG . str_repeat('x', 500)));
    }

    public function test_an_empty_file_is_refused(): void
    {
        $this->expectRefusal('/empty/');

        $this->store()->store($this->upload('empty.png', ''));
    }

    public function test_a_failed_upload_is_reported_in_words(): void
    {
        // "Error 1" tells the user nothing, and the two size errors need
        // different settings changed.
        $file = new \Libxa\Http\UploadedFile('x.png', 'image/png', '/nonexistent', UPLOAD_ERR_INI_SIZE, 0);

        $this->expectRefusal('/upload_max_filesize/');

        $this->store()->store($file);
    }

    // ── the allow-list ───────────────────────────────────────────────────

    public function test_the_allow_list_does_not_contain_anything_executable(): void
    {
        $dangerous = ['php', 'php5', 'phtml', 'phar', 'sh', 'bash', 'exe', 'bat',
                      'cmd', 'com', 'js', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py',
                      'htaccess', 'svg', 'html', 'htm', 'xhtml'];

        foreach ($dangerous as $extension) {
            self::assertArrayNotHasKey(
                $extension,
                MediaStore::ALLOWED,
                ".{$extension} must never be accepted",
            );
        }
    }

    public function test_every_allowed_extension_names_the_types_it_must_be(): void
    {
        foreach (MediaStore::ALLOWED as $extension => $types) {
            self::assertNotSame([], $types, ".{$extension} accepts anything, which defeats the check");

            foreach ($types as $type) {
                self::assertMatchesRegularExpression('#^[a-z]+/[a-z0-9.+-]+$#', $type);
            }
        }
    }

    // ── size parsing ─────────────────────────────────────────────────────

    public function test_sizes_are_parsed_from_configuration(): void
    {
        self::assertSame(10_485_760, MediaStore::parseSize('10mb'));
        self::assertSame(524_288, MediaStore::parseSize('512kb'));
        self::assertSame(1_073_741_824, MediaStore::parseSize('1gb'));
        self::assertSame(2048, MediaStore::parseSize('2048'));
        self::assertSame(4096, MediaStore::parseSize(4096));
    }

    public function test_an_unparseable_size_falls_back_rather_than_becoming_zero(): void
    {
        // A zero limit refuses every upload, which looks like the feature is
        // broken rather than like a typo in configuration.
        self::assertSame(10_485_760, MediaStore::parseSize('ten megabytes'));
        self::assertSame(10_485_760, MediaStore::parseSize(''));
        self::assertSame(10_485_760, MediaStore::parseSize(-5));
    }
}
