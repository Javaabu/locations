<?php

return [

    'types' => [
        'country' => ['singular' => 'Country', 'plural' => 'Countries'],
        'state' => ['singular' => 'State', 'plural' => 'States'],
        'city' => ['singular' => 'City', 'plural' => 'Cities'],
        'atoll' => ['singular' => 'Atoll', 'plural' => 'Atolls'],
        'island' => ['singular' => 'Island', 'plural' => 'Islands'],
    ],

    'fields' => [
        'name' => 'Name',
        'dhivehi_name' => 'Dhivehi name',
        'official_name' => 'Official name',
        'official_dhivehi_name' => 'Official Dhivehi name',
        'code' => 'Code',
        'alpha3_code' => 'Alpha-3 code',
        'numeric_code' => 'Numeric code',
        'calling_code' => 'Calling code',
        'dhivehi_code' => 'Dhivehi code',
        'letter_code' => 'Letter code',
        'iso_code' => 'ISO 3166-2 code',
        'type' => 'Type',
        'country' => 'Country',
        'state' => 'State',
        'atoll' => 'Atoll',
        'capital' => 'Capital',
        'category' => 'Category',
        'is_capital' => 'Atoll capital',
        'is_city' => 'Governed by a city council',
        'sort_order' => 'Order',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'area' => 'Area',
    ],

    'help' => [
        'country_code' => 'The ISO 3166-1 alpha-2 code, e.g. MV',
        'calling_code' => 'Without the +, e.g. 960',
        'state_code' => 'The ISO 3166-2 code, e.g. LK-1',
        'atoll_code' => 'The abbreviation, e.g. HA',
        'iso_code' => 'e.g. MV-07',
        'island_code' => 'The feature code of the island register, e.g. LD0442',
    ],

    'sections' => [
        'details' => 'Details',
        'geography' => 'Geography',
        'related' => 'Related',
    ],

    'island_categories' => [
        'residential' => 'Residential',
        'tourism' => 'Tourism',
        'industrial' => 'Industrial',
        'institutional' => 'Institutional',
        'uninhabited' => 'Uninhabited',
    ],

    'units' => [
        'hectares' => 'ha',
        'area' => ':area ha',
    ],

    'actions' => [
        'new' => 'New :type',
        'create' => 'Create :type',
        'save' => 'Save',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'actions' => 'Actions',
        'back' => 'Back to :type',
        'filter' => 'Filter',
        'search' => 'Search',
    ],

    'search' => 'Search :type',

    'messages' => [
        'created' => ':type created',
        'updated' => ':type updated',
        'deleted' => ':type deleted',
        'confirm_delete' => 'Delete this :type? This can\'t be undone.',
        'has_children' => 'This :type can\'t be deleted while it has :children.',
        'empty' => 'No :type yet',
        'empty_filtered' => 'No :type match your search.',
    ],

    'selects' => [
        'placeholder' => 'Nothing selected',
        'choose_parent' => 'Choose the :parent first',
    ],

    'yes' => 'Yes',
    'no' => 'No',

];
