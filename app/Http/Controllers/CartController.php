<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

}
