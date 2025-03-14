<x-head/>
<div class="bg-black h-screen">
    <div class="pt-10 container mx-auto">
        <h1 class="text-2xl font-bold text-white">Daftar Produk Kami</h1>
        <div class="mt-2 pb-7 text-xs text-white">Lupakan tabungan anda, belanja sebanyak banyaknya sekarang juga.</div>
    
        <div class="pt-6 grid grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="p-4 bg-white border border-slate-400">
                <img src="{{ $product['images'][0]['src'] }}" alt="{{ $product['title'] }}" class="w-full bg-slate-600 h-[300px] object-cover">
                <h2 class="text-xl font-semibold text-gray-900 mt-4">{{ $product['title'] }}</h2>
                <a href="/produk/{{ $product['id'] }}" class="px-3 py-2 bg-red-700 text-white mt-3 block w-max">View Product</a>

                {{-- <button 
                    onclick="addToCart({{ $product['variants'][0]['id'] }}, '{{ $product['title'] }}', '{{ $product['images'][0]['src'] }}')" 
                    class="mt-3 px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition cursor-pointer"
                >
                    Add to Cart
                </button> --}}
            </div>
            @endforeach
        </div>
    </div>
    
   <script>
      function addToCart(variantId, title, image) {
          fetch("/cart/add", {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
              },
              body: JSON.stringify({
                  variant_id: variantId,
                  title: title,
                  image: image,
                  quantity: 1
              })
          })
          .then(response => response.json())
          .then(data => {
              alert(data.message); 
          })
          .catch(error => {
              console.error("Error:", error);
          });
      }
   </script>
</div>
</html>
