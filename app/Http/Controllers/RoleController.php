<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Http\Requests\RoleRequest;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $query = Role::withCount('permissions')
            ->where('is_active', '1')
            ->where('role_code', '!=', 'SUPERADMIN'); // Hide SUPER ADMIN from list
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('role_code', 'like', $search . '%')
                  ->orWhere('role_name', 'like', $search . '%')
                  ->orWhere('role_description', 'like', $search . '%');
            });
        }
        
        $roles = $query->paginate(10)->withQueryString();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if user is Super Admin
        $isSuperAdmin = auth()->user()->roles->contains('role_code', 'SUPERADMIN');
        
        // Filter permissions - exclude permission and menu management for non-super admins
        $permissions = Permission::where('is_active', '1');
        if (!$isSuperAdmin) {
            $permissions = $permissions->whereNotIn('permission_code', [
                'permissions.view',
                'permissions.create',
                'permissions.edit',
                'permissions.delete',
                'menus.view',
                'menus.create',
                'menus.edit',
                'menus.delete'
            ]);
        }
        $permissions = $permissions->get();
        
        // Filter menus - exclude User Management > Permissions and Menus for non-super admins
        $menus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->with(['children' => function($query) use ($isSuperAdmin) {
                $query->where('is_active', '1');
                if (!$isSuperAdmin) {
                    $query->whereNotIn('menu_code', ['permissions', 'menus']);
                }
            }])
            ->orderBy('menu_order')
            ->get();
            
        return view('roles.create', compact('permissions', 'menus', 'isSuperAdmin'));
    }

    public function store(RoleRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();

            // Call stored procedure sp_add_ms_role
            $result = \DB::select('CALL sp_add_ms_role(?, ?, ?, ?, ?)', [
                $data['role_code'],
                $data['role_name'],
                $data['role_description'],
                $userId,
                $uniqueId
            ]);

            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create role. No response from database.');
            }
            
            $response = $result[0];

            // Handle response based on return_code
            if ($response->return_code == 200) {
                // Get the created role by unique_id
                $role = Role::where('unique_id', $uniqueId)->first();

                if ($role) {
                    // Attach permissions
                    if ($request->has('permissions')) {
                        foreach ($request->permissions as $permissionId) {
                            \DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
                                $role->role_id,
                                $permissionId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                        }
                    }

                    // Attach menus
                    if ($request->has('menus')) {
                        foreach ($request->menus as $menuId) {
                            \DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', [
                                $role->role_id,
                                $menuId,
                                $userId,
                                (string) Str::uuid(),
                            ]);
                        }
                    }

                    return redirect()->route('roles.index')
                        ->with('success', 'Role created successfully.');
                }
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['role_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating role.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in RoleController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(Role $role)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent editing SUPER ADMIN role
        if ($role->role_code === 'SUPERADMIN') {
            abort(403, 'SUPER ADMIN role cannot be edited.');
        }
        
        // Check if user is Super Admin
        $isSuperAdmin = auth()->user()->roles->contains('role_code', 'SUPERADMIN');
        
        $role->load('permissions', 'menus');
        
        // Filter permissions - exclude permission and menu management for non-super admins
        $permissions = Permission::where('is_active', '1');
        if (!$isSuperAdmin) {
            $permissions = $permissions->whereNotIn('permission_code', [
                'permissions.view',
                'permissions.create',
                'permissions.edit',
                'permissions.delete',
                'menus.view',
                'menus.create',
                'menus.edit',
                'menus.delete'
            ]);
        }
        $permissions = $permissions->get();
        
        // Filter menus - exclude User Management > Permissions and Menus for non-super admins
        $menus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->with(['children' => function($query) use ($isSuperAdmin) {
                $query->where('is_active', '1');
                if (!$isSuperAdmin) {
                    $query->whereNotIn('menu_code', ['permissions', 'menus']);
                }
            }])
            ->orderBy('menu_order')
            ->get();
            
        $rolePermissions = $role->permissions->pluck('permission_id')->toArray();
        $roleMenus = $role->menus->pluck('menu_id')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'menus', 'rolePermissions', 'roleMenus', 'isSuperAdmin'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent updating SUPER ADMIN role
        if ($role->role_code === 'SUPERADMIN') {
            return redirect()->route('roles.index')->with('error', 'SUPER ADMIN role cannot be updated.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Call stored procedure sp_update_ms_role
            $result = \DB::select('CALL sp_update_ms_role(?, ?, ?, ?, ?)', [
                $data['role_code'],
                $data['role_name'],
                $data['role_description'],
                $userId,
                $role->unique_id
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update role. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                // Sync permissions (soft delete approach)
                $requestedPermissions = $request->has('permissions') ? $request->permissions : [];
                
                // Get existing permissions
                $existingPermissions = \DB::table('ms_role_permissions')
                    ->where('role_id', $role->role_id)
                    ->get();
                
                // Deactivate unchecked permissions
                foreach ($existingPermissions as $existing) {
                    if (!in_array($existing->permission_id, $requestedPermissions)) {
                        \DB::select('CALL sp_update_ms_role_permission(?, ?, ?, ?, ?)', [
                            $role->role_id,
                            $existing->permission_id,
                            '0',
                            $userId,
                            $existing->unique_id
                        ]);
                    }
                }
                
                // Activate or insert checked permissions
                foreach ($requestedPermissions as $permissionId) {
                    $existing = \DB::table('ms_role_permissions')
                        ->where('role_id', $role->role_id)
                        ->where('permission_id', $permissionId)
                        ->first();
                    
                    if ($existing) {
                        // Reactivate if exists
                        \DB::select('CALL sp_update_ms_role_permission(?, ?, ?, ?, ?)', [
                            $role->role_id,
                            $permissionId,
                            '1',
                            $userId,
                            $existing->unique_id
                        ]);
                    } else {
                        // Insert new
                        \DB::select('CALL sp_add_ms_role_permission(?, ?, ?, ?)', [
                            $role->role_id,
                            $permissionId,
                            $userId,
                            (string) Str::uuid()
                        ]);
                    }
                }

                // Sync menus (soft delete approach)
                $requestedMenus = $request->has('menus') ? $request->menus : [];
                
                // Get existing menus
                $existingMenus = \DB::table('ms_role_menus')
                    ->where('role_id', $role->role_id)
                    ->get();
                
                // Deactivate unchecked menus
                foreach ($existingMenus as $existing) {
                    if (!in_array($existing->menu_id, $requestedMenus)) {
                        \DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
                            $role->role_id,
                            $existing->menu_id,
                            '0',
                            $userId,
                            $existing->unique_id
                        ]);
                    }
                }
                
                // Activate or insert checked menus
                foreach ($requestedMenus as $menuId) {
                    $existing = \DB::table('ms_role_menus')
                        ->where('role_id', $role->role_id)
                        ->where('menu_id', $menuId)
                        ->first();
                    
                    if ($existing) {
                        // Reactivate if exists
                        \DB::select('CALL sp_update_ms_role_menu(?, ?, ?, ?, ?)', [
                            $role->role_id,
                            $menuId,
                            '1',
                            $userId,
                            $existing->unique_id
                        ]);
                    } else {
                        // Insert new
                        \DB::select('CALL sp_add_ms_role_menu(?, ?, ?, ?)', [
                            $role->role_id,
                            $menuId,
                            $userId,
                            (string) Str::uuid()
                        ]);
                    }
                }

                return redirect()->route('roles.index')
                    ->with('success', 'Role updated successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['role_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while updating role.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in RoleController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in RoleController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Role $role)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('roles.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Prevent deleting SUPER ADMIN role
        if ($role->role_code === 'SUPERADMIN') {
            return redirect()->route('roles.index')->with('error', 'Cannot delete SUPER ADMIN role.');
        }
        
        // Prevent deleting ADMIN role
        if ($role->role_code === 'ADMIN') {
            return redirect()->route('roles.index')->with('error', 'Cannot delete ADMIN role.');
        }
        
        $role->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
