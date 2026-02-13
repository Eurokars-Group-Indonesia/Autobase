<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SearchHistory::with('user')
            ->orderBy('executed_date', 'desc');
        
        // Filter by transaction type
        if ($request->has('transaction_type') && $request->transaction_type != '') {
            $query->where('transaction_type', $request->transaction_type);
        }
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('executed_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('executed_date', '<=', $request->date_to);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        
        $histories = $query->paginate($perPageValue)->withQueryString();
        
        // Get all users for filter
        $users = \App\Models\User::where('is_active', '1')
            ->orderBy('full_name')
            ->get();
        
        return view('search-history.index', compact('histories', 'users'));
    }
}
