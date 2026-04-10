@extends('admin::layouts.admin')

@section('title', ucfirst($resource) . ' - LibAdmin')

@section('header', ucfirst($resource))

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="/admin/resources/{{ $resource }}/create" style="padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 4px;">
            + Create New {{ ucfirst($resource) }}
        </a>
    </div>

    @if($items && count($items) > 0)
        <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f1f5f9;">
                    @php
                        $columns = array_keys((array) $items[0]);
                    @endphp
                    @foreach($columns as $column)
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0;">{{ ucfirst($column) }}</th>
                    @endforeach
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        @foreach($columns as $column)
                            <td style="padding: 12px;">{{ $item->$column ?? '-' }}</td>
                        @endforeach
                        <td style="padding: 12px;">
                            <a href="/admin/resources/{{ $resource }}/{{ $item->id }}" style="color: #4f46e5; text-decoration: none; margin-right: 10px;">View</a>
                            <a href="/admin/resources/{{ $resource }}/{{ $item->id }}/edit" style="color: #f59e0b; text-decoration: none; margin-right: 10px;">Edit</a>
                            <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST" style="display: inline;">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; text-decoration: underline;" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 8px;">
            <p style="color: #64748b;">No {{ $resource }} found. Create your first one!</p>
        </div>
    @endif
@endsection
