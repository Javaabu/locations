{{-- Links to a location's pages, for what the user may do. $links and $abilities come from the controller. --}}
<div class="flex flex-wrap items-center gap-2" data-location-actions>
    @if(($show_view ?? true) && ($abilities['view'] ?? false))
        <x-forms::link-button :url="$links['show']" color="secondary" size="sm" start-icon="eye">
            {{ __('locations::strings.actions.view') }}
        </x-forms::link-button>
    @endif

    @if(($show_edit ?? true) && ($abilities['update'] ?? false))
        <x-forms::link-button :url="$links['edit']" color="secondary" size="sm" start-icon="pencil">
            {{ __('locations::strings.actions.edit') }}
        </x-forms::link-button>
    @endif

    @if($abilities['delete'] ?? false)
        <x-forms::form
            method="DELETE"
            :action="$links['destroy']"
            data-confirm="{{ __('locations::strings.messages.confirm_delete', ['type' => $labels['singular']]) }}"
            onsubmit="return window.confirm(this.dataset.confirm)">
            <x-forms::submit color="danger" size="sm" start-icon="trash-2" :animate="false">{{ __('locations::strings.actions.delete') }}</x-forms::submit>
        </x-forms::form>
    @endif
</div>
