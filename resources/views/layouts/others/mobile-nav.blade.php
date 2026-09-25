@if(Auth::check())
    @php
        $userType = Auth::user()->user_type;
        $currRoute = Route::currentRouteName();
    @endphp

    @if($userType === 'customer')
    <!-- Customer Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
        <a href="{{ route('dashboard.index') }}" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>{{ _lang('Home') }}</span>
        </a>

        <a href="{{ route('transfer.own_account_transfer') }}" class="nav-item {{ Request::is('portal/transfer*') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i>
            <span>{{ _lang('Transfer') }}</span>
        </a>

        <!-- Center Quick Deposit Button -->
        <a href="{{ route('deposit.automatic_methods') }}" class="nav-item nav-item-center {{ Request::is('portal/deposit*') ? 'active' : '' }}">
            <div class="center-btn">
                <i class="fas fa-plus"></i>
            </div>
            <span>{{ _lang('Deposit') }}</span>
        </a>

        <a href="{{ route('customer_reports.account_statement') }}" class="nav-item {{ Request::is('portal/reports*') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i>
            <span>{{ _lang('Passbook') }}</span>
        </a>

        <a href="{{ route('profile.membership_details') }}" class="nav-item {{ (Request::is('profile*') || Request::is('portal/profile*')) ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span>{{ _lang('Profile') }}</span>
        </a>
    </nav>
    @else
    <!-- Admin & Staff Mobile Bottom Navigation Bar -->
    <nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
        <a href="{{ route('dashboard.index') }}" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>{{ _lang('Dashboard') }}</span>
        </a>

        <a href="{{ route('members.index') }}" class="nav-item {{ Request::is('admin/members*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>{{ _lang('Members') }}</span>
        </a>

        <!-- Center Deposit/Transaction Button -->
        <a href="{{ route('transactions.create') }}?type=deposit" class="nav-item nav-item-center {{ Request::is('admin/transactions/create*') ? 'active' : '' }}">
            <div class="center-btn">
                <i class="fas fa-plus"></i>
            </div>
            <span>{{ _lang('Deposit') }}</span>
        </a>

        <a href="{{ route('reports.account_statement') }}" class="nav-item {{ Request::is('admin/reports*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>{{ _lang('Reports') }}</span>
        </a>

        <a href="{{ route('profile.edit') }}" class="nav-item {{ Request::is('profile*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i>
            <span>{{ _lang('Settings') }}</span>
        </a>
    </nav>
    @endif
@endif
