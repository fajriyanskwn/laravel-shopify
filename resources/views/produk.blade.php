<x-head/>
<body class="container mx-auto">
    <div class="p-6 bg-gray-100 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-5">Shopping Cart</h1>
    
        <div class="mt-6 grid grid-cols-4 gap-4">
            @foreach($products as $product)
            <div class="p-4 bg-white rounded-lg shadow">
                <img src="{{ $product['images'][0]['src'] }}" alt="{{ $product['title'] }}" class="w-full h-[300px] object-cover rounded-lg">
                <h2 class="text-xl font-semibold text-gray-900 mt-4">{{ $product['title'] }}</h2>
                <p class="text-gray-600">{!! $product['body_html'] !!}</p>
                <a href="/produk/{{ $product['id'] }}" class="px-3 py-2 bg-blue-700 text-white mt-3 block w-max">View Product</a>

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
</body>
</html>
