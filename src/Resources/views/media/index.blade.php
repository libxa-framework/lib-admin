@extends('admin::layouts.admin')

@section('title', 'Media')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Media</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Uploads are checked by their contents, not by the name or the type the
                browser reports.
            </p>
        </div>
    </div>

    @if (errors()->has('file'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800
                    dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
            {{ errors()->first('file') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800
                    dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/admin/media') }}" enctype="multipart/form-data"
          class="mb-8 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6">
        @csrf

        <input type="file" name="file" required
               class="block w-full text-sm text-gray-600 dark:text-gray-300
                      file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-4 file:py-2
                      file:text-sm file:font-medium file:text-white hover:file:bg-gray-700">

        <div class="mt-4 flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Accepted: {{ implode(', ', array_keys(\Libxa\Admin\Media\MediaStore::ALLOWED)) }}
            </p>

            <button type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Upload
            </button>
        </div>
    </form>

    @if (empty($media))
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center
                    text-gray-500 dark:text-gray-400">
            Nothing uploaded yet.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Name</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Type</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Size</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Uploaded</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($media as $item)
                        @php
                            // Rows arrive as stdClass from Atlas.
                            $row = (object) $item;
                        @endphp
                        <tr>
                            {{-- Escaped, because the original filename came from
                                 whoever uploaded it. --}}
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $row->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row->mime_type }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ number_format(((int) $row->size) / 1024) }} KB
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row->created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ url('/admin/media/' . $row->id) }}"
                                      onsubmit="return confirm('Delete this file?')">
                                    @csrf
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit"
                                            class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
