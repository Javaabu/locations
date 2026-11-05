@extends($layout)

@section($layout_section)
    <div class="flex flex-col gap-6" data-locations-index="{{ $resource }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>

            @if($can['create'])
                <x-forms::link-button :url="$urls['create']" start-icon="plus">
                    {{ __('locations::strings.actions.new', ['type' => $labels['singular']]) }}
                </x-forms::link-button>
            @endif
        </div>

        @include('locations::resources.partials.status')

        <div class="locations-filters" data-locations-filters>
            {{ $filters }}
        </div>

        <x-forms::table :model="$resource" no-checkbox>
            <x-slot:headers>
                @foreach($columns as $column)
                    <x-forms::table.heading :label="$column['label']" />
                @endforeach
                <x-forms::table.heading :label="__('locations::strings.actions.actions')" />
            </x-slot:headers>

            <x-slot:rows>
                @forelse($rows as $row)
                    <x-forms::table.row :model="$row['id']" :name="$resource" no-checkbox data-location-row="{{ $row['id'] }}">
                        @foreach($columns as $column)
                            <x-forms::table.cell :name="$column['key']" :label="$column['label']">
                                @if($loop->first && $row['can']['view'])
                                    <a href="{{ $row['links']['show'] }}" class="font-medium hover:underline" dir="{{ $column['dir'] }}">{{ $row['cells'][$column['key']] }}</a>
                                @else
                                    <span dir="{{ $column['dir'] }}">{{ $row['cells'][$column['key']] }}</span>
                                @endif
                            </x-forms::table.cell>
                        @endforeach
                        <x-forms::table.cell name="actions" :label="__('locations::strings.actions.actions')">
                            @include('locations::resources.partials.actions', ['links' => $row['links'], 'abilities' => $row['can'], 'show_view' => false])
                        </x-forms::table.cell>
                    </x-forms::table.row>
                @empty
                    <x-forms::table.empty-row :columns="count($columns) + 1" no-checkbox>
                        <p class="font-medium">
                            {{ $filtered
                                ? __('locations::strings.messages.empty_filtered', ['type' => $labels['plural']])
                                : __('locations::strings.messages.empty', ['type' => $labels['plural']]) }}
                        </p>
                    </x-forms::table.empty-row>
                @endforelse
            </x-slot:rows>

            @if($locations->hasPages())
                <x-slot:pagination>
                    <x-forms::pagination :paginator="$locations" />
                </x-slot:pagination>
            @endif
        </x-forms::table>
    </div>
@endsection
