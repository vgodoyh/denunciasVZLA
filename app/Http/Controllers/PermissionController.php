<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('permiso_index'),403);

        $permission = Permission::orderBy('id', 'desc')->get();
        return view('permission.index', compact('permission'));
    }

    public function create()
    {
        abort_if(Gate::denies('permiso_create'),403);

        return view('permission.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,NULL,id,guard_name,' . ($request->guard_name ?? 'web'),
        ]);

        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permission.index')->with('guardar', 'ok-guardar');
    }

    public function edit(Permission $permission)
    {
        abort_if(Gate::denies('permiso_edit'),403);

        return view('permission.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id . ',id,guard_name,' . ($request->guard_name ?? $permission->guard_name),
        ]);

        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? $permission->guard_name,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permission.index')->with('modificar', 'ok-modificar');
    }

    public function destroy(Permission $permission)
    {
        abort_if(Gate::denies('permiso_destroy'),403);

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permission.index')->with('eliminar', 'ok-eliminar');
    }
}