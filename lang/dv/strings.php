<?php

return [

    'types' => [
        'country' => ['singular' => 'ޤައުމު', 'plural' => 'ޤައުމުތައް'],
        'state' => ['singular' => 'ސްޓޭޓް', 'plural' => 'ސްޓޭޓްތައް'],
        'city' => ['singular' => 'ސިޓީ', 'plural' => 'ސިޓީތައް'],
        'atoll' => ['singular' => 'އަތޮޅު', 'plural' => 'އަތޮޅުތައް'],
        'island' => ['singular' => 'ރަށް', 'plural' => 'ރަށްތައް'],
    ],

    'fields' => [
        'name' => 'ނަން',
        'dhivehi_name' => 'ދިވެހި ނަން',
        'official_name' => 'ރަސްމީ ނަން',
        'official_dhivehi_name' => 'ރަސްމީ ދިވެހި ނަން',
        'code' => 'ކޯޑު',
        'alpha3_code' => 'އަލްފާ-3 ކޯޑު',
        'numeric_code' => 'ނަންބަރު ކޯޑު',
        'calling_code' => 'ކޯލިންގ ކޯޑު',
        'dhivehi_code' => 'ދިވެހި ކޯޑު',
        'letter_code' => 'އަކުރު ކޯޑު',
        'iso_code' => 'ISO 3166-2 ކޯޑު',
        'type' => 'ބާވަތް',
        'country' => 'ޤައުމު',
        'state' => 'ސްޓޭޓް',
        'atoll' => 'އަތޮޅު',
        'capital' => 'ވެރިރަށް',
        'category' => 'ބާވަތް',
        'is_capital' => 'އަތޮޅުގެ ވެރިރަށް',
        'is_city' => 'ސިޓީ ކައުންސިލަކުން ހިންގާ',
        'sort_order' => 'ތަރުތީބު',
        'latitude' => 'ލެޓިޓިއުޑް',
        'longitude' => 'ލޮންގިޓިއުޑް',
        'area' => 'ބޮޑުމިން',
    ],

    'help' => [
        'country_code' => 'ISO 3166-1 އަލްފާ-2 ކޯޑު، މިސާލަކަށް MV',
        'calling_code' => '+ ނުލާ، މިސާލަކަށް 960',
        'state_code' => 'ISO 3166-2 ކޯޑު، މިސާލަކަށް LK-1',
        'atoll_code' => 'ކުރުކޮށް ލިޔާ ގޮތް، މިސާލަކަށް HA',
        'iso_code' => 'މިސާލަކަށް MV-07',
        'island_code' => 'ރަށްތަކުގެ ރަޖިސްޓަރީގެ ފީޗަރ ކޯޑު، މިސާލަކަށް LD0442',
    ],

    'sections' => [
        'details' => 'ތަފްސީލު',
        'geography' => 'ޖުޣްރާފީ މަޢުލޫމާތު',
        'related' => 'ގުޅުންހުރި',
    ],

    'island_categories' => [
        'residential' => 'ރައްޔިތުން ދިރިއުޅޭ ރަށް',
        'tourism' => 'ފަތުރުވެރިކަމުގެ ރަށް',
        'industrial' => 'ސިނާޢީ ރަށް',
        'institutional' => 'މުއައްސަސާތަކުގެ ރަށް',
        'uninhabited' => 'މީހުން ނުދިރިއުޅޭ ރަށް',
    ],

    'units' => [
        'hectares' => 'ހެކްޓަރު',
        'area' => ':area ހެކްޓަރު',
    ],

    'actions' => [
        'new' => 'އައު :type',
        'create' => ':type އުފައްދާ',
        'save' => 'ސޭވް ކުރައްވާ',
        'view' => 'ބަލާ',
        'edit' => 'ބަދަލުކުރޭ',
        'delete' => 'ފޮހެލާ',
        'actions' => 'ކަންތައްތައް',
        'back' => ':type އަށް އެނބުރި',
        'filter' => 'ފިލްޓަރު ކުރޭ',
        'search' => 'ހޯދާ',
    ],

    'search' => ':type ހޯދާ',

    'messages' => [
        'created' => ':type އުފެއްދިއްޖެ',
        'updated' => ':type ބަދަލުކުރެވިއްޖެ',
        'deleted' => ':type ފޮހެލިއްޖެ',
        'confirm_delete' => 'މި :type ފޮހެލަންވީތޯ؟ މިކަން އަނބުރާ ނުކުރެވޭނެ.',
        'has_children' => ':children ހުރިހާ ހިނދަކު މި :type ނުފޮހެލޭނެ.',
        'empty' => 'އަދި އެއްވެސް :type ނެތް',
        'empty_filtered' => 'ހޯދި ގޮތާ ދިމާވާ :type ނެތް.',
    ],

    'yes' => 'އާން',
    'no' => 'ނޫން',

];
