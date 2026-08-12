<?php

declare(strict_types=1);

namespace Libxa\Admin\Media;

use Libxa\Atlas\DB;
use Libxa\Http\UploadedFile;
use RuntimeException;

/**
 * Accepts an upload, or refuses it with a reason.
 *
 * File upload is where an admin panel turns into remote code execution, so
 * this is deliberately strict and deliberately boring.
 *
 * The rules, and why each one is here:
 *
 * **The type is detected, never taken from the request.** `UploadedFile::
 * getMimeType()` returns `$_FILES['type']`, which the client sets. An attacker
 * uploading a PHP script simply declares it `image/png`. The bytes are what
 * get inspected.
 *
 * **The extension and the detected type must agree.** Either alone is not
 * enough: a `.png` extension proves nothing, and a detected `image/png` on a
 * file called `shell.php` is a polyglot, which is a real technique.
 *
 * **The stored name is generated.** The client's filename never becomes a
 * path. That removes traversal, null bytes, overlong names, reserved Windows
 * device names, and the double-extension trick in one go. The original is kept
 * as a label in the database, where it is data rather than a path.
 *
 * **The list is an allow-list.** Deny-lists lose: `.php5`, `.phtml`, `.phar`,
 * uppercase, trailing dots, and whatever the next handler mapping adds.
 */
final class MediaStore
{
    public const TABLE = 'media';

    /**
     * Extensions accepted, and the types the bytes must actually be.
     *
     * SVG is absent on purpose. It is XML, it can carry script, and browsers
     * execute it in the origin that serves it, so an SVG upload is stored XSS
     * on your own admin domain. Anyone who needs it should add it knowingly,
     * along with a sanitiser.
     *
     * @var array<string, list<string>>
     */
    public const ALLOWED = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'avif' => ['image/avif'],
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'csv' => ['text/plain', 'text/csv', 'application/csv'],
        'zip' => ['application/zip'],
    ];

    public function __construct(
        private readonly mixed $storage,
        private readonly string $disk = 'public',
        private readonly string $directory = 'media',
        private readonly int $maxBytes = 10_485_760,
        /** @var array<string, list<string>> */
        private readonly array $allowed = self::ALLOWED,
    ) {
    }

    /**
     * Store an upload and record it.
     *
     * @throws RuntimeException with a message safe to show the user
     * @return array<string, mixed> the row that was written
     */
    public function store(UploadedFile $file, int|string|null $actorId = null): array
    {
        $this->assertUploadSucceeded($file);

        $size = $file->getSize();

        if ($size <= 0) {
            throw new RuntimeException('That file is empty.');
        }

        if ($size > $this->maxBytes) {
            throw new RuntimeException(sprintf(
                'That file is %s. The limit is %s.',
                $this->human($size),
                $this->human($this->maxBytes),
            ));
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! isset($this->allowed[$extension])) {
            throw new RuntimeException(sprintf(
                'Files of type .%s are not accepted. Allowed: %s.',
                $extension === '' ? '(none)' : $extension,
                implode(', ', array_keys($this->allowed)),
            ));
        }

        $detected = $this->detectType($file);

        if (! in_array($detected, $this->allowed[$extension], true)) {
            // The extension and the bytes disagree. Either a mistake or a
            // polyglot; neither is worth storing.
            throw new RuntimeException(
                "That file claims to be .{$extension} but its contents are {$detected}.",
            );
        }

        $storedName = $this->generateName($extension);
        $path = $this->directory . '/' . date('Y/m') . '/' . $storedName;

        if (! $this->storage->put($path, $this->contentsOf($file))) {
            throw new RuntimeException('The file could not be saved.');
        }

        $row = [
            'admin_user_id' => $actorId === null ? null : (string) $actorId,
            // The original name, for display. Data, never a path.
            'name' => $this->safeLabel($file->getClientOriginalName()),
            'file_name' => $storedName,
            'mime_type' => $detected,
            'size' => $size,
            'disk' => $this->disk,
            'path' => $path,
            'custom_properties' => json_encode([], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::table(self::TABLE)->insert($row);

        return $row;
    }

    /**
     * Delete a stored file and its record.
     *
     * The path comes from the row, never from the request. Deleting by a
     * client-supplied path is how an upload feature becomes an arbitrary file
     * delete.
     */
    public function delete(int|string $id): bool
    {
        $row = DB::table(self::TABLE)->where('id', $id)->first();

        if ($row === null) {
            return false;
        }

        $path = is_object($row) ? ($row->path ?? null) : ($row['path'] ?? null);

        if (is_string($path) && $path !== '') {
            try {
                $this->storage->delete($path);
            } catch (\Throwable) {
                // The row still goes. A file that cannot be removed is a
                // stray on disk; a row pointing at nothing is a broken page.
            }
        }

        DB::table(self::TABLE)->where('id', $id)->delete();

        return true;
    }

    /** @return list<mixed> */
    public function recent(int $limit = 60): array
    {
        try {
            return DB::table(self::TABLE)->orderByLatest()->limit($limit)->get();
        } catch (\Throwable) {
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    private function assertUploadSucceeded(UploadedFile $file): void
    {
        if ($file->isValid()) {
            return;
        }

        // PHP's own codes, said in words. "Error 1" tells the user nothing,
        // and the two size errors mean different settings need changing.
        throw new RuntimeException(match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE => 'That file is larger than upload_max_filesize allows.',
            UPLOAD_ERR_FORM_SIZE => 'That file is larger than the form allows.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Try again.',
            UPLOAD_ERR_NO_FILE => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary directory configured.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.',
            default => 'The upload failed.',
        });
    }

    /**
     * What the file actually is, according to its bytes.
     *
     * finfo when it is available, falling back to a signature check. The
     * fallback is not a substitute for finfo, but it is far better than
     * trusting the header the client sent, which is the alternative.
     */
    private function detectType(UploadedFile $file): string
    {
        $path = $this->pathOf($file);

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $type = finfo_file($finfo, $path);
                finfo_close($finfo);

                if (is_string($type) && $type !== '') {
                    return $type;
                }
            }
        }

        return $this->detectFromSignature($path);
    }

    private function detectFromSignature(string $path): string
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return 'application/octet-stream';
        }

        $head = (string) fread($handle, 16);
        fclose($handle);

        return match (true) {
            str_starts_with($head, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($head, "\x89PNG\r\n\x1A\n") => 'image/png',
            str_starts_with($head, 'GIF87a'), str_starts_with($head, 'GIF89a') => 'image/gif',
            str_starts_with($head, '%PDF-') => 'application/pdf',
            str_starts_with($head, "PK\x03\x04") => 'application/zip',
            str_starts_with($head, 'RIFF') && str_contains($head, 'WEBP') => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    private function contentsOf(UploadedFile $file): string
    {
        $contents = @file_get_contents($this->pathOf($file));

        if ($contents === false) {
            throw new RuntimeException('The uploaded file could not be read.');
        }

        return $contents;
    }

    /** The temporary path, read through the object rather than assumed. */
    private function pathOf(UploadedFile $file): string
    {
        // UploadedFile keeps it protected, so it is read reflectively rather
        // than by adding a getter to the framework for one caller.
        $reflection = new \ReflectionObject($file);

        if ($reflection->hasProperty('tmpName')) {
            $property = $reflection->getProperty('tmpName');
            $property->setAccessible(true);

            $value = $property->getValue($file);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        throw new RuntimeException('The uploaded file has no readable path.');
    }

    /**
     * A stored filename that owes nothing to the client.
     *
     * Random, lowercase, one dot, known extension. Nothing here can traverse a
     * directory, collide meaningfully, or be interpreted as anything but the
     * extension it ends in.
     */
    private function generateName(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    /**
     * The original filename, reduced to something safe to store and show.
     *
     * Control characters removed, length capped. It is escaped again on
     * output, but a control character in a database column is a problem for
     * every consumer, not only HTML.
     */
    private function safeLabel(string $name): string
    {
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name);

        if ($name === '') {
            return 'untitled';
        }

        return mb_substr($name, 0, 180);
    }

    private function human(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /** Parse "10mb", "512kb" or a plain byte count. */
    public static function parseSize(string|int $value, int $default = 10_485_760): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : $default;
        }

        $value = strtolower(trim($value));

        if (preg_match('/^(\d+(?:\.\d+)?)\s*(gb|mb|kb|b)?$/', $value, $m) !== 1) {
            return $default;
        }

        $number = (float) $m[1];

        return (int) match ($m[2] ?? 'b') {
            'gb' => $number * 1_073_741_824,
            'mb' => $number * 1_048_576,
            'kb' => $number * 1024,
            default => $number,
        };
    }
}
