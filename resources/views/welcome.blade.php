<!DOCTYPE html>

<head> 
 <title>Laravel + Vue Chat</title>
 @vite(['resources/js/app.js'])
</head>
<body>
 <div class="min-h-screen bg-gray-100" id="app">
  <header >
    <nav>
      <div id="app">
        @yield('content')
      </div>
    </nav>
  </header>
</div>
</body>

</html>