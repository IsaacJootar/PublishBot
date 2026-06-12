<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $totals = [
            'month' => $user->sales()->whereMonth('sale_date', now()->month)->whereYear('sale_date', now()->year)->sum('amount'),
            'allTime' => $user->sales()->sum('amount'),
            'soldMonth' => $user->sales()->whereMonth('sale_date', now()->month)->whereYear('sale_date', now()->year)->count(),
            'currency' => $user->sales()->latest('sale_date')->value('currency') ?? 'USD',
        ];

        $byTopic = $user->sales()
            ->selectRaw('topic, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('topic')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byPlatform = $user->sales()
            ->selectRaw('platform, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();

        $monthly = $user->sales()
            ->selectRaw("strftime('%Y-%m', sale_date) as month, SUM(amount) as total")
            ->whereRaw("sale_date >= date('now', '-12 months')")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $sales = $user->sales()->orderByDesc('sale_date')->paginate(20);

        $runs = $user->runs()->where('status', 'completed')->orderByDesc('created_at')->get(['id', 'topic']);

        return view('sales.index', compact('totals', 'byTopic', 'byPlatform', 'monthly', 'sales', 'runs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'product_type' => ['required', 'in:ebook,workbook,printable,bundle'],
            'platform' => ['required', 'in:amazon,etsy,gumroad'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'run_id' => ['nullable', 'exists:pipeline_runs,id'],
        ]);

        auth()->user()->sales()->create($request->only([
            'topic', 'product_type', 'platform', 'amount', 'currency', 'sale_date', 'notes', 'run_id',
        ]));

        return back()->with('toast', ['message' => 'Sale logged.', 'type' => 'success']);
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        abort_if($sale->user_id !== auth()->id(), 403);
        $sale->delete();

        return back()->with('toast', ['message' => 'Sale removed.', 'type' => 'success']);
    }
}
