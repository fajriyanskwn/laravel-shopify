<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>Halo</title>
   <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
   <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

   <!-- Meta Pixel Code -->
   <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '637929395456355');
      fbq('track', 'PageView');
      </script>
      <noscript><img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=637929395456355&ev=PageView&noscript=1"
      /></noscript>
      <!-- End Meta Pixel Code -->

      <script>
         fbq('track', 'Purchase', {currency: "USD", value: 30.00});
       </script>
      
</head>
<nav class=" bg-black">
   <div class="container mx-auto h-[80px] border-b flex justify-center items-center gap-4">
      <a href="/" class="bg-red-800 text-white px-4 py-1.5">home</a>
      <a href="/produk" class="bg-red-800 text-white px-4 py-1.5">produk</a>
      <a href="/cart" class="bg-red-800 text-white px-4 py-1.5">cart</a>
   </div>
</nav>