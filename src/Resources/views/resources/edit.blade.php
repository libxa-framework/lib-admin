@extends('admin::layouts.admin')

@section('title', 'Edit ' . ucfirst($resource) . ' - LibAdmin')

@section('header', 'Edit ' . ucfirst($resource))

@section('content')
    @if($item)
        <div style="max-width: 800px;">
            <div style="margin-bottom: 20px;">
                <a href="/admin/resources/{{ $resource }}" style="color: #4f46e5; text-decoration: none;">&larr; Back to {{ ucfirst($resource) }}</a>
            </div>

            <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <input type="hidden" name="_method" value="PUT">

                @php
                    $properties = (array) $item;
                @endphp
                @foreach($properties as $key => $value)
                    @if($key !== 'id' && $key !== 'password')
                        <div style="margin-bottom: 20px;">
                            <label for="{{ $key }}" style="display: block; margin-bottom: 8px; font-weight: 500;">{{ ucfirst($key) }}</label>
                            @if($key === 'email')
                                <input type="email" id="{{ $key }}" name="{{ $key }}" value="{{ $value ?? '' }}" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
                            @elseif($key === 'role')
                                <select id="{{ $key }}" name="{{ $key }}" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
                                    <option value="admin" {{ $value === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="editor" {{ $value === 'editor' ? 'selected' : '' }}>Editor</option>
                                    <option value="viewer" {{ $value === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                </select>
                            @else
                                <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $value ?? '' }}" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
                            @endif
                        </div>
                    @endif
                @endforeach

                <div style="margin-bottom: 20px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Update {{ ucfirst($resource) }}
                    </button>
                    <a href="/admin/resources/{{ $resource }}" style="padding: 10px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 4px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    @else
        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 8px;">
            <p style="color: #64748b;">{{ ucfirst($resource) }} not found.</p>
            <a href="/admin/resources/{{ $resource }}" style="color: #4f46e5; text-decoration: none;">Back to {{ ucfirst($resource) }}</a>
        </div>
    @endif
@endsection
