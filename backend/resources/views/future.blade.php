<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>LedgerScope Future Modules</title>
        @if (app()->environment('local'))
            <script type="module" src="{{ rtrim((string) config('app.frontend_url'), '/') }}/src/future.ts"></script>
        @else
            <link rel="stylesheet" href="{{ rtrim((string) config('app.frontend_url'), '/') }}/assets/main.css">
            <script type="module" src="{{ rtrim((string) config('app.frontend_url'), '/') }}/assets/future.js"></script>
        @endif
    </head>
    <body>
        <div id="app" data-page="{{ json_encode($page) }}"></div>
    </body>
</html>
