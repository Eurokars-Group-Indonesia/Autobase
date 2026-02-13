<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Http\Requests\DealerRequest;
use Illuminate\Support\Str;

class DealerController extends Controller
{
    public function index()
    {
        $query = Dealer::where('is_active', '1')->orderBy('dealer_name');
        
        // Search functionality
        if (request()->has('search') && request('search') != '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('dealer_code', 'like', $search . '%')
                  ->orWhere('dealer_name', 'like', $search . '%')
                  ->orWhere('city', 'like', $search . '%');
            });
        }
        
        $dealers = $query->paginate(10)->withQueryString();
        return view('dealers.index', compact('dealers'));
    }

    public function create()
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('dealers.create');
    }

    public function store(DealerRequest $request)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $uniqueId = (string) Str::uuid();
            $userId = auth()->id();
            
            // Call stored procedure sp_add_ms_dealer
            $result = \DB::select('CALL sp_add_ms_dealer(?, ?, ?, ?, ?)', [
                $userId,
                $data['dealer_name'],
                $data['dealer_code'],
                $data['city'] ?? null,
                $uniqueId
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create dealer. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('dealers.index')
                    ->with('success', 'Dealer created successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['dealer_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while creating dealer.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in DealerController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in DealerController@store: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function edit(Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('dealers.edit', compact('dealer'));
    }

    public function update(DealerRequest $request, Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $data = $request->validated();
            $userId = auth()->id();
            
            // Call stored procedure sp_update_ms_dealer
            $result = \DB::select('CALL sp_update_ms_dealer(?, ?, ?, ?, ?)', [
                $userId,
                $data['dealer_name'],
                $data['dealer_code'],
                $data['city'] ?? null,
                $dealer->unique_id
            ]);
            
            // Check result from stored procedure
            if (empty($result)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update dealer. No response from database.');
            }
            
            $response = $result[0];
            
            // Handle response based on return_code
            if ($response->return_code == 200) {
                return redirect()->route('dealers.index')
                    ->with('success', 'Dealer updated successfully.');
            } elseif ($response->return_code == 404) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message);
            } elseif ($response->return_code == 409) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['dealer_name' => $response->return_message]);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $response->return_message ?? 'An error occurred while updating dealer.');
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error in DealerController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error in DealerController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function destroy(Dealer $dealer)
    {
        // Double check permission
        if (!auth()->user()->hasPermission('dealers.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        $dealer->update(['is_active' => '0', 'updated_by' => auth()->id()]);
        return redirect()->route('dealers.index')->with('success', 'Dealer deleted successfully.');
    }
}
