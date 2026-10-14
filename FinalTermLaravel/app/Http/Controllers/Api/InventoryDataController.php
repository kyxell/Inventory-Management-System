<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryDataController
{
    public function index(Request $request): JsonResponse
    {
        return match ($request->query('type')) {
            'categories' => response()->json([
                'categories' => DB::table('categories')->orderBy('id')->get(['id', 'name']),
            ]),
            'inventory_summary' => $this->summary(),
            'all_products' => response()->json([
                'products' => DB::table('products')->orderBy('name')->get(['id', 'name']),
            ]),
            'products_by_category' => $this->productsByCategory($request),
            'product', 'product_for_import', 'product_for_deliver' => $this->product($request),
            'product_for_edit' => $this->productForEdit($request),
            'product_for_delete' => $this->productForDelete($request),
            'inventory_charts' => $this->charts($request),
            'products' => $this->products($request),
            'logs' => $this->logs($request),
            default => response()->json(['error' => 'Invalid type parameter'], 400),
        };
    }

    private function summary(): JsonResponse
    {
        $lowStock = DB::table('products as p')
            ->leftJoin('inventory_levels as i', 'p.id', '=', 'i.product_id')
            ->whereRaw('COALESCE(i.quantity, 0) < 10')
            ->count();

        return response()->json([
            'totalProducts' => DB::table('products')->count(),
            'totalInventory' => (int) DB::table('inventory_levels')->sum('quantity'),
            'lowStock' => $lowStock,
        ]);
    }

    private function productsByCategory(Request $request): JsonResponse
    {
        $categoryId = (int) $request->query('category_id', 0);

        if ($categoryId < 1) {
            return response()->json(['products' => []]);
        }

        return response()->json([
            'products' => DB::table('products')
                ->where('category_id', $categoryId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    private function product(Request $request): JsonResponse
    {
        $id = (int) $request->query('id', 0);
        $product = DB::table('products as p')
            ->join('inventory_levels as i', 'p.id', '=', 'i.product_id')
            ->where('p.id', $id)
            ->first(['p.id', 'p.name', 'i.quantity']);

        if (!$product) {
            return response()->json(['error' => $id ? 'Product not found.' : 'Product ID is required.'], 404);
        }

        return response()->json([
            'id' => (int) $product->id,
            'name' => $product->name,
            'quantity' => (int) $product->quantity,
        ]);
    }

    private function productForEdit(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Forbidden - Admin access required'], 403);
        }

        $id = (int) $request->query('id', 0);
        $product = DB::table('products')
            ->where('id', $id)
            ->first(['id', 'name', 'description', 'category_id', 'unit_price', 'cost_price']);

        return $product
            ? response()->json($product)
            : response()->json(['error' => $id ? 'Product not found.' : 'Product ID is required.'], 404);
    }

    private function productForDelete(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return response()->json(['error' => 'Forbidden - Admin access required'], 403);
        }

        $id = (int) $request->query('id', 0);
        $product = DB::table('products')->where('id', $id)->first(['id', 'name']);

        return $product
            ? response()->json($product)
            : response()->json(['error' => $id ? 'Product not found.' : 'Product ID is required.'], 404);
    }

    private function charts(Request $request): JsonResponse
    {
        $query = DB::table('inventory_logs as l')
            ->join('products as p', 'l.product_id', '=', 'p.id')
            ->orderBy('l.created_at');

        if ((int) $request->query('product_id', 0) > 0) {
            $query->where('l.product_id', (int) $request->query('product_id'));
        }
        if ((int) $request->query('category_id', 0) > 0) {
            $query->where('p.category_id', (int) $request->query('category_id'));
        }

        $charts = [];
        foreach ($query->get(['l.product_id', 'p.name as product_name', 'l.new_quantity', 'l.created_at']) as $row) {
            $id = (int) $row->product_id;
            $charts[$id] ??= ['name' => $row->product_name, 'labels' => [], 'values' => []];
            $charts[$id]['labels'][] = Carbon::parse($row->created_at)->format('M d, Y H:i');
            $charts[$id]['values'][] = (int) $row->new_quantity;
        }

        return response()->json(['chartsData' => $charts]);
    }

    private function products(Request $request): JsonResponse
    {
        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('inventory_levels as i', 'p.id', '=', 'i.product_id')
            ->select([
                'p.id',
                'p.name',
                'c.name as category',
                'p.unit_price',
                'p.cost_price',
                'p.created_at',
                DB::raw('COALESCE(i.quantity, 0) as quantity'),
            ])
            ->orderBy('p.id');

        if ((int) $request->query('category_id', 0) > 0) {
            $query->where('p.category_id', (int) $request->query('category_id'));
        }

        $isAdmin = $this->isAdmin();
        $products = $query->get()->map(function ($product) use ($isAdmin) {
            $product->id = (int) $product->id;
            $product->quantity = (int) $product->quantity;
            $product->isAdmin = $isAdmin;

            return $product;
        });

        $lowStockProducts = DB::table('products as p')
            ->leftJoin('inventory_levels as i', 'p.id', '=', 'i.product_id')
            ->whereRaw('COALESCE(i.quantity, 0) < 10')
            ->orderByRaw('COALESCE(i.quantity, 0) asc')
            ->get(['p.id', 'p.name', DB::raw('COALESCE(i.quantity, 0) as quantity')])
            ->map(function ($product) {
                $product->id = (int) $product->id;
                $product->quantity = (int) $product->quantity;

                return $product;
            });

        return response()->json([
            'products' => $products,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    private function logs(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $query = DB::table('inventory_logs as l')
            ->join('products as p', 'l.product_id', '=', 'p.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id');

        if ((int) $request->query('product_id', 0) > 0) {
            $query->where('l.product_id', (int) $request->query('product_id'));
        }
        if ((int) $request->query('category_id', 0) > 0) {
            $query->where('p.category_id', (int) $request->query('category_id'));
        }
        if ($request->query('direction') === 'in') {
            $query->where('l.quantity_change', '>=', 0);
        } elseif ($request->query('direction') === 'out') {
            $query->where('l.quantity_change', '<', 0);
        }
        if (!empty($filters['date_from'])) {
            $query->where('l.created_at', '>=', $filters['date_from'].' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('l.created_at', '<=', $filters['date_to'].' 23:59:59');
        }

        $total = (clone $query)->count('l.id');
        $logs = $query
            ->orderByDesc('l.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'l.id',
                'l.product_id',
                'p.name as product_name',
                'c.name as category_name',
                'l.quantity_change',
                'l.new_quantity',
                'l.created_at',
            ])
            ->map(function ($log) {
                $log->quantity_change = (int) $log->quantity_change;
                $log->new_quantity = (int) $log->new_quantity;
                $log->direction = $log->quantity_change >= 0 ? 'in' : 'out';

                return $log;
            });

        return response()->json([
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    private function isAdmin(): bool
    {
        return (int) Auth::user()->role_id === 1;
    }
}