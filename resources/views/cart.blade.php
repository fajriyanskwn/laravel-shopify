
<x-head/>
<div class="container mx-auto mt-10">
   <h1 class="text-2xl font-bold">Keranjang Belanja Anda</h1>
   <div class="mt-2 pb-7 text-xs">Lupakan tabungan anda, belanja sebanyak banyaknya sekarang juga.</div>

   @if(session('success'))
       <div class="bg-green-100 p-3 text-green-700 mb-4">{{ session('success') }}</div>
   @endif

   @if(count($cart) > 0)
       <table class="w-full border-collapse border border-gray-300">
           <thead>
               <tr class="bg-gray-200">
                   <th class="p-3 border w-[230px]">Gambar</th>
                   <th class="p-3 border">Nama Produk</th>
                   <th class="p-3 border hidden">Variant ID</th>
                   <th class="p-3 border w-[100px]">Jumlah</th>
                   <th class="p-3 border w-[100px]">Aksi</th>
               </tr>
           </thead>
           <tbody>
               @foreach($cart as $item)
                   <tr class="border">
                       <td class="p-2 border"><img src="{{ $item['img'] }}" alt="" class="w-full h-[200px] object-cover"></td>
                       <td class="p-3 border">{{ $item['title'] }}</td>
                       <td class="p-3 border hidden">{{ $item['variant_id'] }}</td>
                       <td class="p-3 text-center">
                           {{-- <button 
                               onclick="updateQuantity({{ $item['variant_id'] }}, -1)" 
                               class="px-2 py-1 bg-gray-300 text-black rounded">−</button> --}}
                           <span id="quantity-{{ $item['variant_id'] }}">{{ $item['quantity'] }}</span>
                           {{-- <button 
                               onclick="updateQuantity({{ $item['variant_id'] }}, 1)" 
                               class="px-2 py-1 bg-gray-300 text-black rounded">+</button> --}}
                       </td>
                       <td class="p-3 border">
                           <form action="{{ route('cart.remove') }}" method="POST" class="w-full flex justify-center">
                               @csrf
                               <input type="hidden" name="variant_id" value="{{ $item['variant_id'] }}">
                               <button type="submit" class="px-3 py-3 bg-red-500 hover:bg-red-800 text-white cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                    <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                  </svg>
                               </button>
                           </form>
                       </td>
                   </tr>
               @endforeach
           </tbody>
       </table>

        <div id="checkout-message" class="mt-3 text-red-500 font-semibold"></div>
        <ul id="checkout-error-list" class="mt-3 text-red-500 font-semibold list-disc list-inside"></ul>
        <div id="loading" class="mt-3 text-blue-500 font-semibold hidden">Checking stock...</div>



       <div class="mt-5">
        <button onclick="proceedToCheckout()" class="px-6 py-3   bg-red-800 text-white cursor-pointer hover:bg-red-700">
            Checkout
        </button>
    </div>
    

       
       <div class="mt-5 hidden">
        <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded cursor-pointer hover:bg-red-700">
                Proceed to Checkout
            </button>
        </form>        
       </div>
   @else
       <p>Your cart is empty.</p>
   @endif
</div>

<script>
    function proceedToCheckout() {
        let messageElement = document.getElementById("checkout-message");
        let loadingElement = document.getElementById("loading");
        let checkoutForm = document.getElementById("checkout-form");

        // Reset pesan & tampilkan loading
        messageElement.innerText = "";
        loadingElement.classList.remove("hidden");

        fetch("{{ route('cart.checkStock') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingElement.classList.add("hidden"); // Sembunyikan loading

            if (data.success) {
                checkoutForm.submit();
            } else {
                messageElement.innerText = data.message; // Tampilkan pesan error
            }
        })
        .catch(error => {
            loadingElement.classList.add("hidden");
            messageElement.innerText = "Terjadi kesalahan dalam proses pengecekan stok.";
        });
    }
</script>


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
    
    