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

    // Panggil Shopify API untuk mendapatkan data stok terbaru
    $shopifyAccessToken = env('SHOPIFYACCESSTOKEN');
    $shopifyDomain = 'qkqarf-pc.myshopify.com';

    $shopifyApiUrl = "https://$shopifyDomain/admin/api/2024-01/variants/$variantId.json";

    $response = Http::withHeaders([
        'X-Shopify-Access-Token' => $shopifyAccessToken,
        'Content-Type' => 'application/json'
    ])->get($shopifyApiUrl);

    if ($response->failed()) {
        return response()->json(['message' => 'Failed to fetch variant data'], 500);
    }

    $variantData = $response->json();

    // Cek apakah response benar
    if (!isset($variantData['variant']['inventory_quantity'])) {
        return response()->json(['message' => 'Invalid variant data received'], 500);
    }

    $stock = $variantData['variant']['inventory_quantity'];

    // Cek apakah item sudah ada di cart dan hitung total quantity yang akan dimasukkan
    $existingQuantity = 0;
    foreach ($cart as $item) {
        if ($item['variant_id'] == $variantId) {
            $existingQuantity = $item['quantity'];
        }
    }

    $newTotalQuantity = $existingQuantity + $quantity;

    // Debugging log
    \Log::info("Variant ID: $variantId, Requested Quantity: $quantity, Existing in Cart: $existingQuantity, Available Stock: $stock, New Total: $newTotalQuantity");

    // Jika total quantity melebihi stok yang tersedia
    if ($newTotalQuantity > $stock) {
        return response()->json(['message' => 'Stock is not enough!'], 400);
    }

    // Update cart
    $found = false;
    foreach ($cart as &$item) {
        if ($item['variant_id'] == $variantId) {
            $item['quantity'] += $quantity; // Tambah jumlah
            $found = true;
            break;
        }
    }

    if (!$found) {
        $cart[] = [
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'img' => $image,
            'title' => $title,
        ];
    }

    Session::put('cart', $cart);

    return response()->json(['message' => 'Added to cart', 'cart' => $cart]);
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
        $found = false;
        foreach ($cart as &$item) {
            if ($item['variant_id'] == $variantId) {
                $item['quantity'] = $newQuantity;  // Perbarui quantity produk
                $found = true;
                break;
            }
        }
        if ($found) {
            session()->put('cart', $cart);
            return response()->json(['success' => true, 'quantity' => $newQuantity]);
        }
        return response()->json(['success' => false], 400);
    }

    public function removeFromCart(Request $request)
    {
        $cart = Session::get('cart', []);
        $cart = array_filter($cart, function ($item) use ($request) {
            return $item['variant_id'] != $request->variant_id;
        });
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

    public function checkStockCart(Request $request)
    {
        $variantId = $request->variant_id;
        $requestedQuantity = $request->quantity;
        $store_name = 'qkqarf-pc';
        $api_version = '2024-01';
        $access_token = env('SHOPIFYACCESSTOKEN');
        $url = "https://$store_name.myshopify.com/admin/api/$api_version/variants/$variantId.json";
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $access_token,
            'Content-Type' => 'application/json',
        ])->get($url);
        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil stok dari Shopify'], 500);
        }
        $shopifyData = $response->json();
        $availableStock = $shopifyData['variant']['inventory_quantity'] ?? 0;

        if ($requestedQuantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "Stok hanya tersedia $availableStock."
            ]);
        }
        return response()->json(['success' => true]);
    }
}
