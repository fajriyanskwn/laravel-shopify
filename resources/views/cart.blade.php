
<x-head/>
<div class="container mx-auto mt-10">
   <h1 class="text-2xl font-bold mb-5">Shopping Cart</h1>

   @if(session('success'))
       <div class="bg-green-100 p-3 text-green-700 mb-4">{{ session('success') }}</div>
   @endif

   @if(count($cart) > 0)
       <table class="w-full border-collapse border border-gray-300">
           <thead>
               <tr class="bg-gray-200">
                   <th class="p-3 border">img</th>
                   <th class="p-3 border">title</th>
                   <th class="p-3 border">Variant ID</th>
                   <th class="p-3 border">Quantity</th>
                   <th class="p-3 border">Actions</th>
               </tr>
           </thead>
           <tbody>
               @foreach($cart as $item)
                   <tr class="border">
                       <td class="p-3 border"><img src="{{ $item['img'] }}" alt="" class="w-16 h-16"></td>
                       <td class="p-3 border">{{ $item['title'] }}</td>
                       <td class="p-3 border">{{ $item['variant_id'] }}</td>
                       <td class="p-3 border flex items-center justify-center space-x-2">
                           <button 
                               onclick="updateQuantity({{ $item['variant_id'] }}, -1)" 
                               class="px-2 py-1 bg-gray-300 text-black rounded">−</button>
                           <span id="quantity-{{ $item['variant_id'] }}">{{ $item['quantity'] }}</span>
                           <button 
                               onclick="updateQuantity({{ $item['variant_id'] }}, 1)" 
                               class="px-2 py-1 bg-gray-300 text-black rounded">+</button>
                       </td>
                       <td class="p-3 border">
                           <form action="{{ route('cart.remove') }}" method="POST" class="inline">
                               @csrf
                               <input type="hidden" name="variant_id" value="{{ $item['variant_id'] }}">
                               <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded">Remove</button>
                           </form>
                       </td>
                   </tr>
               @endforeach
           </tbody>
       </table>

       <div class="mt-5">
           <form action="{{ route('cart.checkout') }}" method="POST">
               @csrf
               <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded cursor-pointer hover:bg-red-700">Proceed to Checkout</button>
           </form>
       </div>
   @else
       <p>Your cart is empty.</p>
   @endif
</div>
<script>
    function updateQuantity(variantId, change) {
    let quantityElement = document.getElementById(`quantity-${variantId}`);
    let newQuantity = parseInt(quantityElement.innerText) + change;

    if (newQuantity < 1) return; // Jangan izinkan quantity lebih kecil dari 1

    fetch("{{ route('cart.update') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({
            variant_id: variantId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            quantityElement.innerText = newQuantity; // Update tampilannya
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}

</script>
    
    