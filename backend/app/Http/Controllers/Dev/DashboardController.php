<?php

namespace App\Http\Controllers\Dev;

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
            'total_views' => (int) Article::sum('view_count'),
        ];

        $recent = Article::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'created_at', 'is_external', 'view_count']);

        $topViews = Article::where('status', 'published')
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'view_count', 'last_viewed_at']);

        return view('admin.dashboard', [
            'stats' => $stats,
            'recent' => $recent,
            'topViews' => $topViews,
            'isDev' => true,
        ]);
    }
}
