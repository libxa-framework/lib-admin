@extends('admin::layouts.admin')

@section('title', ucfirst($resource) . ' Details - LibAdmin')

@section('header', ucfirst($resource) . ' Details')

@section('content')
    @if($item)
        <div style="max-width: 800px;">
            <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="margin-bottom: 20px;">
                    <a href="/admin/resources/{{ $resource }}" style="color: #4f46e5; text-decoration: none;">&larr; Back to {{ ucfirst($resource) }}</a>
                </div>

                <h1 style="margin-bottom: 30px;">{{ ucfirst($resource) }} #{{ $item->id }}</h1>

                <div style="display: grid; gap: 20px;">
                    @php
                        $properties = (array) $item;
                    @endphp
                    @foreach($properties as $key => $value)
                        <div style="padding: 15px; background: #f8fafc; border-radius: 4px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #64748b;">{{ ucfirst($key) }}</label>
                            <span style="font-size: 16px;">{{ $value ?? '-' }}</span>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <a href="/admin/resources/{{ $resource }}/{{ $item->id }}/edit" style="padding: 10px 20px; background: #f59e0b; color: white; text-decoration: none; border-radius: 4px;">
                        Edit
                    </a>
                    <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST" style="display: inline;">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;" onclick="return confirm('Are you sure you want to delete this {{ $resource }}?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 8px;">
            <p style="color: #64748b;">{{ ucfirst($resource) }} not found.</p>
            <a href="/admin/resources/{{ $resource }}" style="color: #4f46e5; text-decoration: none;">Back to {{ ucfirst($resource) }}</a>
        </div>
    @endif
@endsection
