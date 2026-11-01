<?php

namespace App\Http\Controllers;

use App\Models\productvariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatusController extends Controller
{
    private const STATUS_GROUPS = [
        'all' => ['label' => 'All', 'statuses' => null],
        'to_ship' => ['label' => 'To ship', 'statuses' => ['To ship']],
        'to_receive' => ['label' => 'To Recieve', 'statuses' => ['To Recieve']],
        'completed' => ['label' => 'Complete', 'statuses' => ['Complete']],
        'cancelled' => ['label' => 'Cancelled', 'statuses' => ['Cancelled']],
    ];

    public function index(Request $request): View
    {
        $selectedStatus = $request->query('status', 'all');
        if (! is_string($selectedStatus) || ! isset(self::STATUS_GROUPS[$selectedStatus])) {
            $selectedStatus = 'all';
        }

        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->orderByDesc('order_date')
            ->orderByDesc('order_no')
            ->get();

        $productIds = DB::table('order_items')
            ->whereIn('order_no', $orders->pluck('order_no'))
            ->distinct()
            ->pluck('product_id');
        $variantImages = productvariant::query()
            ->whereIn('product_id', $productIds)
            ->get()
            ->mapWithKeys(fn (productvariant $variant) => [
                $variant->product_id.'|'.$variant->color => $variant->image,
            ])
            ->all();

        $reviewedProductIds = $request->user()
            ->reviews()
            ->distinct()
            ->pluck('product_id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

        $orderStatusCounts = DB::table('orders')
            ->where('customer_id', $request->user()->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusGroups = collect(self::STATUS_GROUPS)
            ->map(fn ($group, $key) => [
                'key' => $key,
                'label' => $group['label'],
                'count' => $key === 'all'
                    ? $orderStatusCounts->sum()
                    : collect($group['statuses'])->sum(fn ($status) => (int) ($orderStatusCounts[$status] ?? 0)),
            ]);

        return view('shop.orders', compact('orders', 'selectedStatus', 'statusGroups', 'reviewedProductIds', 'variantImages'));
    }
}
