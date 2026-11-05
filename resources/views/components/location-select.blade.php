<x-forms::select2
    :name="$name"
    :label="$label"
    :options="$options"
    :child="$child_selector"
    :ajax-url="$ajax_url"
    :filter-field="$filter_field"
    data-location-select="{{ $type }}"
    {{ $attributes }} />
