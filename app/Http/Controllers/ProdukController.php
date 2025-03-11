<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProdukController extends Controller
{
    public function index()
    {
        $store_name = 'qkqarf-pc';
        $api_version = '2024-01';
        $access_token = env('SHOPIFYACCESSTOKEN');

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $access_token,
        ])->get("https://{$store_name}.myshopify.com/admin/api/{$api_version}/products.json");

        return view('produk', ['products' => $response->json()['products']]);

    }
    public function show($id)
    {
        $store_name = 'qkqarf-pc';
        $api_version = '2024-01';
        $access_token = env('SHOPIFYACCESSTOKEN');

        $url = "https://$store_name.myshopify.com/admin/api/$api_version/products/$id.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $access_token,
            'Content-Type' => 'application/json',
        ])->get($url);

        $product = $response->json()['product'] ?? null;

        if (!$product) {
            return abort(404, 'Product not found');
        }

        return view('product-detail', compact('product'));
    }
}
