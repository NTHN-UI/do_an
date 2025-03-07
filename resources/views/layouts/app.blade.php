<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Laravel App')</title>
</head>
<body>

<header>
    <h1>Welcome to My Laravel App</h1>
</header>

<main>
      {{-- Phần nội dung sẽ được render ở đây --}}
</main>@yield('content')

<footer>
    <p>Copyright &copy; {{ date('Y') }}</p>
</footer>

</body>
</html>
