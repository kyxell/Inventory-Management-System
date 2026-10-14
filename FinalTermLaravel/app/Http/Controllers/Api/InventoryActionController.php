<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryLevel;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryActionController
{
    public function store(Request $request): JsonResponse
    {
        $values = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productId = DB::transaction(function () use ($values) {
            $product = Product::create([
                'name' => trim($values['name']),
                'description' => trim($values['description']),
                'category_id' => $values['category_id'],
                'unit_price' => $values['unit_price'],
                'cost_price' => $values['cost_price'],
            ]);

            InventoryLevel::create([
                'product_id' => $product->id,
                'quantity' => $values['quantity'],
            ]);

            InventoryLog::create([
                'product_id' => $product->id,
                'quantity_change' => $values['quantity'],
                'new_quantity' => $values['quantity'],
            ]);

            return $product->id;
        });

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully.',
            'productId' => $productId,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Forbidden - Admin access required.'], 403);
        }

        $values = $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
        ]);

        Product::where('id', $values['id'])->update([
            'name' => trim($values['name']),
            'description' => trim($values['description']),
            'category_id' => $values['category_id'],
            'unit_price' => $values['unit_price'],
            'cost_price' => $values['cost_price'],
        ]);

        return response()->json(['success' => true, 'message' => 'Product updated successfully.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Forbidden - Admin access required.'], 403);
        }

        $values = $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
        ]);

        DB::transaction(function () use ($values) {
            InventoryLog::where('product_id', $values['id'])->delete();
            InventoryLevel::where('product_id', $values['id'])->delete();
            Product::where('id', $values['id'])->delete();
        });

        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }

    public function import(Request $request): JsonResponse
    {
        return $this->adjustStock($request, true);
    }

    public function deliver(Request $request): JsonResponse
    {
        return $this->adjustStock($request, false);
    }

    private function adjustStock(Request $request, bool $import): JsonResponse
    {
        $values = $request->validate([
            'id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $result = DB::transaction(function () use ($values, $import) {
            $product = Product::find($values['id']);
            $level = InventoryLevel::where('product_id', $values['id'])->lockForUpdate()->first();

            if (!$product || !$level) {
                return ['error' => 'Product not found.', 'status' => 404];
            }

            $quantity = (int) $values['quantity'];
            $current = (int) $level->quantity;
            if (!$import && $quantity > $current) {
                return ['error' => 'Cannot export more than the current stock.', 'status' => 422];
            }

            $newQuantity = $import ? $current + $quantity : $current - $quantity;
            $level->quantity = $newQuantity;
            $level->save();

            InventoryLog::create([
                'product_id' => $product->id,
                'quantity_change' => $import ? $quantity : -$quantity,
                'new_quantity' => $newQuantity,
            ]);

            return [
                'success' => true,
                'message' => ($import ? 'Product imported' : 'Product exported')." successfully. New stock: {$newQuantity}",
                'newQuantity' => $newQuantity,
            ];
        });

        $status = $result['status'] ?? 200;
        unset($result['status']);

        return response()->json($result, $status);
    }

    private function isAdmin(): bool
    {
        return (int) Auth::user()->role_id === 1;
    }
}