 <x-head />
<!-- Notyf CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
<!-- Notyf JS -->
<script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>


<div class="container mx-auto mt-10 mb-20 flex items-start gap-10">
    <div class="w-[35%]">
        @foreach ($product['images'] as $item)
            <div class="bg-slate-300 mb-4">
                <img id="imageproduct" src="{{ $item['src'] ?? '' }}" alt="{{ $product['title'] }}" class="w-full h-[60vh] object-contain object-center">
            </div>
        @endforeach
    </div>

    <div class="w-[65%] flex flex-col justify-center sticky top-[40px]">
        <h1 class="text-2xl font-bold mb-2" id="">{{ $product['title'] }}</h1>
        <p class="text-gray-700">{!! $product['body_html'] !!}</p>
        <p class="text-lg font-semibold mt-7">
            Price: <span id="selected_price">{{ $product['variants'][0]['price'] }}</span> USD
        </p>

        <h3 class="text-lg font-semibold mt-7">Specifications</h3>
        <ul class="list-disc pl-5">
            @foreach ($product['options'] as $option)
                <li><strong>{{ $option['name'] }}:</strong> 
                    @foreach ($option['values'] as $value)
                        {{ $value }}@if (!$loop->last), @endif
                    @endforeach
                </li>
            @endforeach
        </ul>

        <!-- Dynamic Variant Selections -->
        <div id="variantSelections"></div>

        <!-- Hidden Inputs -->
        <input type="hidden" name="justtitle" value="{{ $product['title'] }}" id="justtitle">
        <input type="hidden" name="variant_id" id="variant_id" value="">
        <input type="hidden" name="variant_title" id="variant_title" value="">
        <input type="hidden" name="price" id="price" value="">
        <input type="hidden" name="stock" id="stock" value="">

        <div class="flex  mt-5 items-end">
            <div class="">
                <label for="quantity" class="block mt-3">Quantity:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" class="border p-2 w-[50px] mt-2" disabled>
            </div>
            
            <!-- Add to Cart Button -->
            <button onclick="addToCart()" id="addToCartBtn" class="px-4 py-2 bg-red-800 cursor-pointer hover:bg-red-900 text-white mt-3 opacity-50" disabled>
                Add to Cart
            </button>
        </div>
        <p id="quantityError" class=""></p>

        <div class="space-y-4 mt-10" x-data="{ open: 0 }">
            <!-- FAQ 1 -->
            <div class="border-b border-gray-300">
                <button @click="open === 1 ? open = null : open = 1" class="w-full flex justify-between items-center py-3 text-left border-b">
                    <span class="font-semibold text-gray-700">Specifications</span>
                    <span x-show="open !== 1">+</span>
                    <span x-show="open === 1">-</span>
                </button>
                <div x-show="open === 1" x-collapse class="text-gray-600 py-3">
                    <h3 class="text-lg font-semibold">Specifications</h3>
                    <ul class="list-disc pl-5">
                        @foreach ($product['options'] as $option)
                            <li><strong>{{ $option['name'] }}:</strong> 
                                @foreach ($option['values'] as $value)
                                    {{ $value }}@if (!$loop->last), @endif
                                @endforeach
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let productVariants = @json($product['variants']);
    let selectedOptions = {};
    function getVariantCount() {
        return @json(count($product['options']));
    }

    function renderVariantSelectors() {
        let container = document.getElementById('variantSelections');
        container.innerHTML = '';
        let variantNames = @json(collect($product['options'])->pluck('name'));
        variantNames.forEach((optionName, index) => {
            let optionKey = `option${index + 1}`;
            let availableOptions = [...new Set(productVariants.map(v => v[optionKey]).filter(o => o))];
            let html = `
                <h3 class="mt-5 text-lg font-semibold">${optionName}:</h3>
                <div class="flex gap-2 mt-2" id="variant_${index}">
                    ${availableOptions.map(option => {
                        let isAvailable = productVariants.some(v => v[optionKey] === option && v.inventory_quantity > 0);
                        let disabledClass = isAvailable ? 'cursor-pointer bg-gray-200 hover:bg-gray-300' : 'bg-gray-400 cursor-not-allowed opacity-50';

                        return `
                            <label class="variant-label px-4 py-2 rounded transition ${disabledClass}"
                                onclick="selectVariant(${index}, '${option}')"
                                data-variant-index="${index}" data-option="${option}"
                                ${isAvailable ? '' : 'disabled'}>
                                <input type="radio" name="variant_${index}" value="${option}" class="hidden">
                                ${option}
                            </label>
                        `;
                    }).join('')}
                </div>
            `;
            container.innerHTML += html;
        });
        checkIfAllVariantsSelected();
    }


    function selectVariant(index, option) {
        let optionElement = event.target.closest('.variant-label');
        if (optionElement.classList.contains('cursor-not-allowed')) return;
        selectedOptions[index] = option;
        document.querySelectorAll(`[data-variant-index="${index}"]`).forEach(el => el.classList.remove('bg-red-500', 'text-white'));
        optionElement.classList.add('bg-red-500', 'text-white');
        updateAvailableOptions(index);
        checkIfAllVariantsSelected();
    }

    function updateAvailableOptions(selectedIndex) {
        Object.keys(selectedOptions).forEach((index) => {
            if (index > selectedIndex) {
                delete selectedOptions[index];
                document.getElementById(`variant_${index}`).innerHTML = '<p class="text-gray-500">Please select previous options first.</p>';
            }
        });

        let filteredVariants = productVariants.filter(variant => {
            return Object.keys(selectedOptions).every(index => variant[`option${+index + 1}`] === selectedOptions[index]);
        });

        let nextIndex = selectedIndex + 1;
        let nextOptions = [...new Set(filteredVariants.map(v => v[`option${nextIndex + 1}`]).filter(o => o))];

        if (nextOptions.length > 0) {
            let nextContainer = document.getElementById(`variant_${nextIndex}`);
            nextContainer.innerHTML = nextOptions.map(option => {
                let isAvailable = filteredVariants.some(v => v[`option${nextIndex + 1}`] === option && v.inventory_quantity > 0);
                let disabledClass = isAvailable ? 'cursor-pointer bg-gray-200 hover:bg-gray-300' : 'bg-gray-400 cursor-not-allowed opacity-50';

                return `
                    <label class="variant-label px-4 py-2 rounded transition ${disabledClass}"
                           onclick="selectVariant(${nextIndex}, '${option}')"
                           data-variant-index="${nextIndex}" data-option="${option}"
                           ${isAvailable ? '' : 'disabled'}>
                        <input type="radio" name="variant_${nextIndex}" value="${option}" class="hidden">
                        ${option}
                    </label>
                `;
            }).join('');
        } else {
            let selectedVariant = filteredVariants.length > 0 ? filteredVariants[0] : null;
            if (selectedVariant) {
                document.getElementById('variant_id').value = selectedVariant.id;
                document.getElementById('variant_title').value = Object.values(selectedOptions).join(' - ');
                document.getElementById('price').value = selectedVariant.price;
                document.getElementById('stock').value = selectedVariant.inventory_quantity;
                document.getElementById('selected_price').innerText = selectedVariant.price;

                if (selectedVariant.inventory_quantity > 0) {
                    document.getElementById('quantity').disabled = false;
                } else {
                    document.getElementById('quantity').disabled = true;
                }
            }
        }
        checkIfAllVariantsSelected();
    }

    function checkIfAllVariantsSelected() {
        let variantCount = getVariantCount(); // Jumlah kategori varian
        let isAllSelected = Object.keys(selectedOptions).length === variantCount;

        let addToCartBtn = document.getElementById('addToCartBtn');

        if (isAllSelected) {
            addToCartBtn.disabled = false;
            addToCartBtn.classList.remove('opacity-50');
        } else {
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('opacity-50');
        }
    }

    let isLoading = false;
    
    function addToCart() {

        let button = document.getElementById('addToCartBtn');
        let originalText = button.innerHTML;
        if (isLoading) return; 
        button.innerHTML = "Adding...";
        button.disabled = true;
        isLoading = true;

    let variantId = document.getElementById('variant_id').value;
    let variantTitle = document.getElementById('variant_title').value;
    let titles = document.getElementById('justtitle').value;
    let img = document.getElementById('imageproduct').src;
    let quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value);
    let stock = parseInt(document.getElementById('stock').value); 
    let quantityError = document.getElementById('quantityError');
    quantityError.textContent = '';
    if (!variantId) {
        quantityError.textContent = 'Please select a variant before adding to cart.';
        resetButton(button, originalText);
        return;
    }

    if (isNaN(quantity) || quantity <= 0) {
        quantityError.textContent = 'Please enter a valid quantity.';
        resetButton(button, originalText);
        return;
    }

    if (quantity > stock) {
        quantityError.classList = 'text-red-600 text-sm mt-3';
        quantityError.textContent = `Out of stock! Available stock: ${stock}`;
        resetButton(button, originalText);
        return;
    }
    const notyf = new Notyf({
        position: {
            x: 'right', // Bisa 'left', 'center', atau 'right'
            y: 'top'   // Bisa 'top' atau 'bottom'
        }
    });
    
    fetch('/cart/add', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        variant_id: variantId,
        title: titles + ' - ' + variantTitle,
        image: img,
        quantity: quantity
    })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // alert(data.message); // Pastikan ini menampilkan pesan dari backend
            notyf.open({
            type: data.message.includes('Stock is not enough') ? 'error' : 'success',
            message: data.message,
            duration: 3000,
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        // alert("Something went wrong!");
        notyf.error("Something went wrong!");
    }).finally(() => {
        resetButton(button, originalText);
    });

    // fetch('/cart/add', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //     },
    //     body: JSON.stringify({
    //         variant_id: variantId,
    //         title: titles + ' - ' + variantTitle,
    //         image: img,
    //         quantity: quantity
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (data.message === 'Added to cart') {
    //         quantityError.textContent = ''; // Hapus error jika berhasil
    //         alert('Item added to cart successfully!');
    //         console.log('Updated Cart:', data.cart);
    //     } else {
    //         quantityError.classList = 'text-green-600 text-sm mt-3';
    //         quantityError.textContent = 'Success add to cart.';
    //     }
    // })
    // .catch(error => console.error('Error:', error));
}


function resetButton(button, originalText) {
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        isLoading = false; // Reset state loading
    }, 1000);
}
    renderVariantSelectors();
</script>
