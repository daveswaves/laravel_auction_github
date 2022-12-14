<!-- resources/views/layout.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Auction DB</title>

<link rel="stylesheet" href="/style.css">

</head>
<body>

<div class="container">
    <!-- Can also use blade components as an alternative -->
    <nav class="navbar fixed nav-full-width">
        <!-- Dropdown (YEARS) -->
        {!! $dd_years !!}
        <!-- Dropdown (AUCTION DATES) -->
        {!! $dd_year_dates !!}
    </nav>
    <div class="h-60px"></div>
    @yield('content')
</div>

</body>
</html>