<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionRequest;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        $query = Permission::where('is_active', '1');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('permission_code', 'like', $search . '%')
                  ->orWhere('permission_name', 'like', $search . '%');
            });
        }
        
        $permissions = $query->paginate(10)->withQueryString();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('permissions.create');
    }

    public function store(PermissionRequest $request)
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();
            
            // Call stored procedure sp_add_ms_permission
            $result = \DB::select('CALL sp_add_ms_permission(?, ?, ?, ?)', [
                $userId,
                $data['permission_code'],
                $data['permission_name'],
                $uniqueId
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create permission. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('permissions.index')
                    ->with('success', 'Permission created successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['permission_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating permission.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in PermissionController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in PermissionController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(Permission $permission)
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('permissions.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Call stored procedure sp_update_ms_permission
            $result = \DB::select('CALL sp_update_ms_permission(?, ?, ?, ?)', [
                $userId,
                $data['permission_code'],
                $data['permission_name'],
                $permission->unique_id
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update permission. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('permissions.index')
                    ->with('success', 'Permission updated successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['permission_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while updating permission.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in PermissionController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in PermissionController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Permission $permission)
    {
        // Only Super Admin can access permissions management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access permissions management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('permissions.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $permission->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
