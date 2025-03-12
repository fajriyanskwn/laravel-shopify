<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    
    public function addToCart(Request $request)
    {
        $cart = Session::get('cart', []);

        $variantId = $request->variant_id;
        $title = $request->title;
        $image = $request->image;
        $quantity = $request->quantity ?? 1;

        // Cek apakah produk sudah ada di dalam cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['variant_id'] == $variantId) {
                $item['quantity'] += $quantity; // Tambahkan jumlah
                $found = true;
                break;
            }
        }

        // Jika belum ada, tambahkan produk ke dalam cart
        if (!$found) {
            $cart[] = [
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'img' => $image,
                'title' => $title,
            ];
        }

        // Save back to session
        Session::put('cart', $cart);

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }

    public function viewCart()
    {
        return response()->json(Session::get('cart', []));
    }

    public function showCart()
    {
        $cart = Session::get('cart', []);
        return view('cart', compact('cart'));
    }


    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
    
        $variantId = $request->variant_id;
        $newQuantity = $request->quantity;
    
        // Periksa apakah produk ada di dalam cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['variant_id'] == $variantId) {
                $item['quantity'] = $newQuantity;  // Perbarui quantity produk
                $found = true;
                break;
            }
        }
    
        // Simpan kembali cart ke session
        if ($found) {
            session()->put('cart', $cart);
            return response()->json(['success' => true, 'quantity' => $newQuantity]);
        }
    
        return response()->json(['success' => false], 400);
    }

    public function removeFromCart(Request $request)
    {
        $cart = Session::get('cart', []);

        // Hapus item berdasarkan variant_id
        $cart = array_filter($cart, function ($item) use ($request) {
            return $item['variant_id'] != $request->variant_id;
        });

        // Simpan kembali ke session
        Session::put('cart', array_values($cart));

        return back()->with('success', 'Product removed from cart');
    }



    public function checkout()
    {
        $cart = Session::get('cart', []);

        $line_items = [];
        foreach ($cart as $item) {
            $line_items[] = [
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity']
            ];
        }

        $store_name = 'qkqarf-pc';
        $checkout_url = "https://{$store_name}.myshopify.com/cart/";

        foreach ($line_items as $item) {
            $checkout_url .= "{$item['variant_id']}:{$item['quantity']},";
        }

        return redirect(rtrim($checkout_url, ','));
    }



    public function checkStockBeforeCheckout(Request $request)
{
    \Log::info('Checking stock for checkout');

    $cart = session('cart', []);
    $store_name = 'qkqarf-pc';
    $api_version = '2024-01';
    $access_token = env('SHOPIFYACCESSTOKEN');

    foreach ($cart as $item) {
        $variantId = $item['variant_id'];
        $requestedQuantity = $item['quantity'];

        $url = "https://$store_name.myshopify.com/admin/api/$api_version/variants/$variantId.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $access_token,
            'Content-Type' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            \Log::error('Failed to fetch stock from Shopify', ['variant_id' => $variantId]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil stok dari Shopify'], 500);
        }

        $shopifyData = $response->json();
        $availableStock = $shopifyData['variant']['inventory_quantity'] ?? 0;

        if ($requestedQuantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Stok untuk {$item['title']} hanya tersedia $availableStock."
            ]);
        }
    }

    return response()->json(['success' => true]);
}


}
