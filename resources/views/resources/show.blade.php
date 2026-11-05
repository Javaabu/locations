@extends($layout)

@section($layout_section)
    <div class="flex flex-col gap-6" data-locations-show="{{ $resource }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold" dir="auto">{{ $title }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <x-forms::link-button :url="$urls['index']" color="secondary" size="sm" start-icon="chevron-left">
                    {{ __('locations::strings.actions.back', ['type' => $labels['plural']]) }}
                </x-forms::link-button>
                @include('locations::resources.partials.actions', ['links' => $urls, 'abilities' => $can, 'show_view' => false])
            </div>
        </div>

        @include('locations::resources.partials.status')

        <x-forms::card :title="__('locations::strings.sections.details')">
            <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2" data-location-details>
                @foreach($details as $detail)
                    <div class="flex flex-col gap-1" data-location-detail="{{ $detail['key'] }}">
                        <dt class="text-sm text-muted-foreground">{{ $detail['label'] }}</dt>
                        <dd dir="{{ $detail['dir'] }}">{{ filled($detail['value']) ? $detail['value'] : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-forms::card>

        @if($related)
            <x-forms::card :title="__('locations::strings.sections.related')">
                <ul class="flex flex-wrap gap-2" data-location-related>
                    @foreach($related as $link)
                        <li>
                            <x-forms::link-button :url="$link['url']" color="secondary" size="sm">{{ $link['label'] }}</x-forms::link-button>
                        </li>
                    @endforeach
                </ul>
            </x-forms::card>
        @endif
    </div>
@endsection
