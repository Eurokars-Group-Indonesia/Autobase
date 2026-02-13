<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Brand;
use App\Models\TransactionHeader;
use App\Models\TransactionBody;

class DashboardController extends Controller
{
    public function index()
    {
        // Get user's brand IDs
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brands for dropdown
        $brands = Brand::whereIn('brand_id', $userBrandIds)
            ->where('is_active', '1')
            ->orderBy('brand_name', 'asc')
            ->get();
        
        // Get brand codes for user's brands
        $userBrandCodes = Brand::whereIn('brand_id', $userBrandIds)
            ->pluck('brand_code')
            ->toArray();
        
        // Get selected year and brand from request
        $selectedYear = request()->get('year', now()->year);
        $selectedBrandId = request()->get('brand_id', null);
        
        // Filter brand codes if specific brand selected
        if ($selectedBrandId) {
            $selectedBrand = Brand::where('brand_id', $selectedBrandId)->first();
            $filterBrandCodes = $selectedBrand ? [$selectedBrand->brand_code] : $userBrandCodes;
        } else {
            $filterBrandCodes = $userBrandCodes;
        }
        
        $data = [
            'totalUsers' => User::where('is_active', '1')->count(),
            'totalTransactionHeaders' => TransactionHeader::whereIn('pos_code', $filterBrandCodes)
                ->where('is_active', '1')
                ->count(),
            'totalTransactionBodies' => TransactionBody::whereIn('pos_code', $filterBrandCodes)
                ->where('is_active', '1')
                ->count(),
            'selectedYear' => $selectedYear,
            'selectedBrandId' => $selectedBrandId,
            'brands' => $brands,
        ];

        // Get transaction header data by invoice_date for selected year
        $headerData = TransactionHeader::whereIn('pos_code', $filterBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('invoice_date')
            ->whereYear('invoice_date', $selectedYear)
            ->selectRaw('MONTH(invoice_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Get transaction body data by date_decard for selected year
        $bodyData = TransactionBody::whereIn('pos_code', $filterBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('date_decard')
            ->whereYear('date_decard', $selectedYear)
            ->selectRaw('MONTH(date_decard) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Prepare chart data
        $data['chartLabels'] = [];
        $data['chartHeaderData'] = [];
        $data['chartBodyData'] = [];

        // Generate all 12 months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = 1; $i <= 12; $i++) {
            $data['chartLabels'][] = $months[$i - 1];
            
            // Find header count for this month
            $headerCount = $headerData->firstWhere('month', $i);
            $data['chartHeaderData'][] = $headerCount ? $headerCount->count : 0;
            
            // Find body count for this month
            $bodyCount = $bodyData->firstWhere('month', $i);
            $data['chartBodyData'][] = $bodyCount ? $bodyCount->count : 0;
        }

        return view('dashboard.index', $data);
    }

    public function getChartData()
    {
        // Get user's brand IDs
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brand codes for user's brands
        $userBrandCodes = Brand::whereIn('brand_id', $userBrandIds)
            ->pluck('brand_code')
            ->toArray();
        
        // Get selected year and brand from request
        $selectedYear = request()->get('year', now()->year);
        $selectedBrandId = request()->get('brand_id', null);
        
        // Filter brand codes if specific brand selected
        if ($selectedBrandId) {
            $selectedBrand = Brand::where('brand_id', $selectedBrandId)->first();
            $filterBrandCodes = $selectedBrand ? [$selectedBrand->brand_code] : $userBrandCodes;
        } else {
            $filterBrandCodes = $userBrandCodes;
        }

        // Get transaction header data by invoice_date for selected year
        $headerData = TransactionHeader::whereIn('pos_code', $filterBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('invoice_date')
            ->whereYear('invoice_date', $selectedYear)
            ->selectRaw('MONTH(invoice_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Get transaction body data by date_decard for selected year
        $bodyData = TransactionBody::whereIn('pos_code', $filterBrandCodes)
            ->where('is_active', '1')
            ->whereNotNull('date_decard')
            ->whereYear('date_decard', $selectedYear)
            ->selectRaw('MONTH(date_decard) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Prepare chart data
        $chartLabels = [];
        $chartHeaderData = [];
        $chartBodyData = [];

        // Generate all 12 months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = $months[$i - 1];
            
            // Find header count for this month
            $headerCount = $headerData->firstWhere('month', $i);
            $chartHeaderData[] = $headerCount ? $headerCount->count : 0;
            
            // Find body count for this month
            $bodyCount = $bodyData->firstWhere('month', $i);
            $chartBodyData[] = $bodyCount ? $bodyCount->count : 0;
        }

        return response()->json([
            'labels' => $chartLabels,
            'headerData' => $chartHeaderData,
            'bodyData' => $chartBodyData,
            'year' => $selectedYear
        ]);
    }
}
