<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\MenuRequest;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index()
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        $query = Menu::with('parent')->where('is_active', '1')->orderBy('menu_order');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('menu_code', 'like', $search . '%')
                  ->orWhere('menu_name', 'like', $search . '%')
                  ->orWhere('menu_url', 'like', $search . '%');
            });
        }
        
        $menus = $query->paginate(10)->withQueryString();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('menus.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $parentMenus = Menu::where('is_active', '1')->whereNull('parent_id')->orderBy('menu_order')->get();
        return view('menus.create', compact('parentMenus'));
    }

    public function store(MenuRequest $request)
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('menus.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();
            
            // Call stored procedure sp_add_ms_menu
            $result = \DB::select('CALL sp_add_ms_menu(?, ?, ?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['menu_code'],
                $data['menu_name'],
                $data['menu_url'] ?? null,
                $data['menu_icon'] ?? null,
                $data['parent_id'] ?? null,
                $data['menu_order'] ?? 0,
                $uniqueId
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create menu. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('menus.index')
                    ->with('success', 'Menu created successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['menu_code' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating menu.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in MenuController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in MenuController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(Menu $menu)
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('menus.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        $parentMenus = Menu::where('is_active', '1')
            ->whereNull('parent_id')
            ->where('menu_id', '!=', $menu->menu_id)
            ->orderBy('menu_order')
            ->get();
        return view('menus.edit', compact('menu', 'parentMenus'));
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('menus.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Call stored procedure sp_update_ms_menu
            $result = \DB::select('CALL sp_update_ms_menu(?, ?, ?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['menu_code'],
                $data['menu_name'],
                $data['menu_url'] ?? null,
                $data['menu_icon'] ?? null,
                $data['parent_id'] ?? null,
                $data['menu_order'] ?? 0,
                $menu->unique_id
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update menu. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('menus.index')
                    ->with('success', 'Menu updated successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['menu_code' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while updating menu.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in MenuController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in MenuController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Menu $menu)
    {
        // Only Super Admin can access menus management
        if (!auth()->user()->roles->contains('role_code', 'SUPERADMIN')) {
            abort(403, 'Only Super Admin can access menus management.');
        }
        
        // Double check permission
        if (!auth()->user()->hasPermission('menus.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $menu->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }
}
