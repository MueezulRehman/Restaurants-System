    @if($r && !$useModernMenuHeader)
        @include('customer.partials.business-header', ['restaurant' => $r])
    @elseif(!$r)
        @include('customer.partials.platform-header', ['platformName' => $platformName, 'platformTagline' => $platformTagline])
    @endif

    @if (session('success'))
        <div class="bg-hut-green text-white text-center py-2 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($r)
        @include('customer.partials.storefront-notice', ['restaurant' => $r])
    @endif

    <main class="customer-content flex-1" data-master-section="content">
        @yield('page-content')
    </main>