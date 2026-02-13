<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\BrandRequest;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $query = Brand::where('is_active', '1')->orderBy('brand_name');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('brand_code', 'like', $search . '%')
                  ->orWhere('brand_name', 'like', $search . '%')
                  ->orWhere('brand_group', 'like', $search . '%')
                  ->orWhere('country_origin', 'like', $search . '%');
            });
        }
        
        $brands = $query->paginate(10)->withQueryString();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('brands.create');
    }

    public function store(BrandRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();
            
            // Call stored procedure sp_add_ms_brand
            $result = \DB::select('CALL sp_add_ms_brand(?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['brand_name'],
                $data['brand_code'],
                $data['brand_group'] ?? null,
                $data['country_origin'] ?? null,
                $uniqueId
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create brand. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('brands.index')
                    ->with('success', 'Brand created successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['brand_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating brand.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in BrandController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in BrandController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Call stored procedure sp_update_ms_brand
            $result = \DB::select('CALL sp_update_ms_brand(?, ?, ?, ?, ?, ?)', [
                $userId,
                $data['brand_name'],
                $data['brand_code'],
                $data['brand_group'] ?? null,
                $data['country_origin'] ?? null,
                $brand->unique_id
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update brand. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('brands.index')
                    ->with('success', 'Brand updated successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['brand_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while updating brand.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in BrandController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in BrandController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Brand $brand)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('brands.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $brand->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
