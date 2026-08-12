<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Admin\Auth\AdminGuard;
use Libxa\Admin\Media\MediaStore;
use Libxa\Http\Request;
use Libxa\Http\Response;
use RuntimeException;
use Throwable;

/**
 * The media library.
 *
 * Every method here used to return a success message and do nothing:
 *
 *     public function upload(Request $request): Response
 *     {
 *         return back()->with('success', 'File uploaded successfully');
 *     }
 *
 * Which is worse than not having the feature, because it reports that it
 * worked. The work now happens in MediaStore, which is where the rules about
 * what may be accepted live.
 */
class MediaController
{
    public function __construct(
        protected AdminGuard $auth,
    ) {
    }

    public function index(): Response
    {
        return view('admin::media.index', [
            'user' => $this->auth->user(),
            'media' => $this->store()->recent(),
        ]);
    }

    public function upload(Request $request): Response
    {
        $file = $request->file('file') ?? $request->file('media');

        if ($file === null) {
            return back()->with('errors', ['file' => ['No file was selected.']]);
        }

        try {
            $stored = $this->store()->store($file, $this->auth->id());
        } catch (RuntimeException $e) {
            // MediaStore's messages are written to be shown: they say which
            // rule was broken and what the limit is, without describing the
            // filesystem.
            return back()->with('errors', ['file' => [$e->getMessage()]]);
        } catch (Throwable $e) {
            $this->report($e);

            return back()->with('errors', ['file' => ['The upload failed.']]);
        }

        return back()->with('success', sprintf('%s uploaded.', $stored['name']));
    }

    public function destroy(string $id): Response
    {
        // Numeric only. The id reaches a WHERE clause, and while the builder
        // binds it, refusing a value that could never be a row id keeps the
        // shape of what is accepted obvious.
        if (preg_match('/^\d+$/', $id) !== 1) {
            return back()->with('errors', ['media' => ['No such file.']]);
        }

        try {
            $deleted = $this->store()->delete($id);
        } catch (Throwable $e) {
            $this->report($e);

            return back()->with('errors', ['media' => ['The file could not be deleted.']]);
        }

        return $deleted
            ? back()->with('success', 'File deleted.')
            : back()->with('errors', ['media' => ['No such file.']]);
    }

    // ─────────────────────────────────────────────────────────────────────

    private function store(): MediaStore
    {
        return app('admin.media');
    }

    private function report(Throwable $e): void
    {
        if (! function_exists('logger')) {
            return;
        }

        $logger = logger();

        if (is_object($logger) && method_exists($logger, 'error')) {
            $logger->error('LibAdmin media: ' . $e->getMessage());
        }
    }
}
