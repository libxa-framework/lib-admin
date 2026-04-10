<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;
use Libxa\Atlas\DB;

class ResourceController
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function index(string $resource): Response
    {
        $user = $this->auth->user();
        $items = DB::table($resource)->get();
        return view('admin::resources.index', compact('resource', 'user', 'items'));
    }

    public function create(string $resource): Response
    {
        $user = $this->auth->user();
        return view('admin::resources.create', compact('resource', 'user'));
    }

    public function store(Request $request, string $resource): Response
    {
        $data = $request->except(['_token', '_method']);
        
        // Get table columns to filter data
        $columns = DB::select("PRAGMA table_info($resource)");
        $validColumns = array_column($columns, 'name');
        
        // Only include columns that exist in the table
        $filteredData = array_intersect_key($data, array_flip($validColumns));
        
        // Hash password if present
        if (isset($filteredData['password'])) {
            $filteredData['password'] = password_hash($filteredData['password'], PASSWORD_BCRYPT);
        }
        
        DB::table($resource)->insert($filteredData);
        return redirect("/admin/resources/$resource");
    }

    public function show(string $resource, string $id): Response
    {
        $user = $this->auth->user();
        $item = DB::table($resource)->where('id', $id)->first();
        return view('admin::resources.show', compact('resource', 'id', 'user', 'item'));
    }

    public function edit(string $resource, string $id): Response
    {
        $user = $this->auth->user();
        $item = DB::table($resource)->where('id', $id)->first();
        return view('admin::resources.edit', compact('resource', 'id', 'user', 'item'));
    }

    public function update(Request $request, string $resource, string $id): Response
    {
        $data = $request->except(['_token', '_method']);
        
        // Get table columns to filter data
        $columns = DB::select("PRAGMA table_info($resource)");
        $validColumns = array_column($columns, 'name');
        
        // Only include columns that exist in the table
        $filteredData = array_intersect_key($data, array_flip($validColumns));
        
        // Hash password if present and not empty
        if (isset($filteredData['password']) && !empty($filteredData['password'])) {
            $filteredData['password'] = password_hash($filteredData['password'], PASSWORD_BCRYPT);
        } elseif (isset($filteredData['password']) && empty($filteredData['password'])) {
            // Remove password field if empty (don't update it)
            unset($filteredData['password']);
        }
        
        DB::table($resource)->where('id', $id)->updateRecord($filteredData);
        return redirect("/admin/resources/$resource");
    }

    public function destroy(string $resource, string $id): Response
    {
        DB::table($resource)->where('id', $id)->delete();
        return redirect("/admin/resources/$resource");
    }
}
