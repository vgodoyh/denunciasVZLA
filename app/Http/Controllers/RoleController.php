<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id', 'desc')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permisos = Permission::orderBy('name')->get();

        return view('roles.create', compact('permisos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $permisos = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permisos);
        }

        return redirect()->route('role.index')->with('guardar', 'ok-guardar');
    }

    public function edit(Role $role)
    {
        $permisos = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permisos', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);

        $permisos = Permission::whereIn('id', $request->permissions ?? [])->get();
        $role->syncPermissions($permisos);

        return redirect()->route('role.index')->with('modificar', 'ok-modificar');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin', 'super-admin'])) {
            return redirect()->route('role.index')->with('eliminar', 'no-eliminar');
        }

        $role->delete();

        return redirect()->route('role.index')->with('eliminar', 'ok-eliminar');
    }
}