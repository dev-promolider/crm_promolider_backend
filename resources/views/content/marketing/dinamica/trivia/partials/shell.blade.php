@php
    $defaultBreadcrumbs = [
        ['label' => 'Marketing', 'url' => route('marketing.tools')],
        ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
        ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
    ];
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
    $active = $active ?? null;
@endphp

<div class="trivia-shell card border-0 shadow-sm mb-2">
    <div class="card-body pb-2">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 mb-lg-0 bg-transparent p-0">
                    @foreach($breadcrumbs as $crumb)
                        <li class="breadcrumb-item {{ ($loop->last || empty($crumb['url'])) ? 'active' : '' }}" {{ ($loop->last || empty($crumb['url'])) ? "aria-current=page" : '' }}>
                            @if(!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="text-primary">{{ $crumb['label'] }}</a>
                            @else
                                <span>{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
            <a href="{{ route('marketing.tools') }}" class="btn btn-link text-muted pr-0">{{ __('locale.Back to') }} {{ __('locale.tools') }}</a>
        </div>
    </div>
</div>
