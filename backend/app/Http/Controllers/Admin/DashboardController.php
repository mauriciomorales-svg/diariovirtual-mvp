<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'draft' => Article::where('status', 'draft')->count(),
            'today' => Article::where('status', 'published')
                ->whereDate('created_at', today())
                ->count(),
            'this_week' => Article::where('status', 'published')
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
            'external' => Article::where('is_external', true)->where('status', 'published')->count(),
            'local' => Article::where('is_external', false)->where('status', 'published')->count(),
        ];

        $recent = Article::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'created_at', 'is_external']);

        return view('admin.dashboard', compact('stats', 'recent'));
    }
}
