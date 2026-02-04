<!DOCTYPE html>
<html lang="en">

@include('layouts.partials.header')

<body>
<div class="wrapper">

    @include('layouts.partials.sidebar')

    <div class="main-panel">

        @include('layouts.partials.navbar')

        <div class="container">
            <div class="page-inner">
                @yield('content')
            </div>
        </div>

        @include('layouts.partials.footer')

    </div>

</div>
</body>
</html>
