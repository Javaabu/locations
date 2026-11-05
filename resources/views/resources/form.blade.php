@extends($layout)

@section($layout_section)
    <div class="flex flex-col gap-6" data-locations-form="{{ $resource }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold" dir="auto">{{ $title }}</h1>
            <x-forms::link-button :url="$urls['index']" color="secondary" size="sm" start-icon="chevron-left">
                {{ __('locations::strings.actions.back', ['type' => $labels['plural']]) }}
            </x-forms::link-button>
        </div>

        @include('locations::resources.partials.status')

        {{ $form }}

        @if($location && $can['delete'])
            <div class="flex justify-end">
                @include('locations::resources.partials.actions', ['links' => $urls, 'abilities' => $can, 'show_view' => false, 'show_edit' => false])
            </div>
        @endif
    </div>
@endsection
