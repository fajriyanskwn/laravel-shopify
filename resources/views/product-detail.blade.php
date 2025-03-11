 <x-head />
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

        <div class="flex gap-5 mt-5 items-end">
            <div class="">
                <label for="quantity" class="block mt-3">Quantity:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" class="border p-2" disabled>
            </div>
            
            <!-- Add to Cart Button -->
            <button onclick="addToCart()" id="addToCartBtn" class="px-4 py-2 bg-blue-500 text-white rounded mt-3 opacity-50" disabled>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<script>
    let productVariants = @json($product['variants']);
    let selectedOptions = {};

    // function getVariantCount() {
    //     let count = 0;
    //     if (productVariants.some(v => v.option1)) count++;
    //     if (productVariants.some(v => v.option2)) count++;
    //     if (productVariants.some(v => v.option3)) count++;
    //     return count;
    // }
    function getVariantCount() {
        return @json(count($product['options']));
    }

    // function renderVariantSelectors() {
    // let container = document.getElementById('variantSelections');
    // container.innerHTML = '';

    // let variantNames = ['option1', 'option2', 'option3'].filter(option => 
    //     productVariants.some(v => v[option] !== null)
    // );

    // let optionNames = @json(collect($product['options'])->pluck('name'));

    // variantNames.forEach((variantName, index) => {
    //     let availableOptions = [...new Set(productVariants.map(v => v[variantName]).filter(o => o))];

    //     let html = `
    //         <h3 class="mt-5 text-lg font-semibold">${optionNames[index]}:</h3>
    //         <div class="flex gap-2 mt-2" id="variant_${index}">
    //             ${availableOptions.map(option => {
    //                 let isAvailable = productVariants.some(v => v[variantName] === option && v.inventory_quantity > 0);
    //                 let disabledClass = isAvailable ? 'cursor-pointer bg-gray-200 hover:bg-gray-300' : 'bg-gray-400 cursor-not-allowed opacity-50';
                    
    //                 return `
    //                     <label class="variant-label px-4 py-2 rounded transition ${disabledClass}"
    //                            onclick="selectVariant(${index}, '${option}')"
    //                            data-variant-index="${index}" data-option="${option}"
    //                            ${isAvailable ? '' : 'disabled'}>
    //                         <input type="radio" name="variant_${index}" value="${option}" class="hidden">
    //                         ${option}
    //                     </label>
    //                 `;
    //             }).join('')}
    //         </div>
    //     `;

    //     container.innerHTML += html;
    // });

    //     checkIfAllVariantsSelected();
    // }
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

    function addToCart() {
        let variantId = document.getElementById('variant_id').value;
        let variantTitle = document.getElementById('variant_title').value;
        let titles = document.getElementById('justtitle').value;
        let img = document.getElementById('imageproduct').src;
        let quantity = document.getElementById('quantity').value;

        if (!variantId) {
            alert('Please select a variant before adding to cart.');
            return;
        }

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
            if (data.message === 'Added to cart') {
                alert('Item added to cart successfully!');
                console.log('Updated Cart:', data.cart);
            } else {
                alert('Failed to add to cart.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    renderVariantSelectors();
</script>
