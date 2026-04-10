@extends('admin::layouts.admin')

@section('title', 'Create ' . ucfirst($resource) . ' - LibAdmin')

@section('header', 'Create ' . ucfirst($resource))

@section('content')
    <div style="max-width: 800px;">
        <form action="/admin/resources/{{ $resource }}" method="POST" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Name</label>
                <input type="text" id="name" name="name" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="role" style="display: block; margin-bottom: 8px; font-weight: 500;">Role</label>
                <select id="role" name="role" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; box-sizing: border-box;">
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Create {{ ucfirst($resource) }}
                </button>
                <a href="/admin/resources/{{ $resource }}" style="padding: 10px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
