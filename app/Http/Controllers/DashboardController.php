<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with real-time statistics
     */
    public function dashboard()
    {
        // Get proposal statistics
        $stats = [
            'total' => Proposal::where('status', '!=', 'draft')->count(),
            'verified' => Proposal::where('status', 'verified')->count(),
            'pending' => Proposal::where('status', 'pending')->count(),
            'drafts' => Proposal::where('status', 'draft')->count(),
        ];

        // Calculate verification percentage
        $stats['verification_percentage'] = $stats['total'] > 0 
            ? round(($stats['verified'] / $stats['total']) * 100, 1) 
            : 0;

        // Get recent proposals for quick overview
        $recentProposals = Proposal::where('status', '!=', 'draft')
            ->latest()
            ->take(5)
            ->get();

        // Get monthly growth (proposals created this month vs last month)
        $thisMonth = Proposal::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'draft')
            ->count();

        $lastMonth = Proposal::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', '!=', 'draft')
            ->count();

        $stats['monthly_growth'] = $thisMonth - $lastMonth;

        return view('dashboard.index', compact('stats', 'recentProposals'));
    }

    /**
     * Show the public verification page
     */
    public function show()
    {
        return view('verify.show');
    }
}