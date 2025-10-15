<?php

return [
    'singular' => 'Vrednost lastnosti',
    'plural' => 'Vrednosti lastnosti',

    'fields' => [
        'value' => 'Vrednost',
        'info_url' => 'URL informacij',
        'image' => 'Slika',
        'sort' => 'Vrstni red',
        'import_file' => 'Uvoz datoteke',
    ],

    'sections' => [
        'value_information' => 'Informacije o vrednosti',
    ],

    'placeholders' => [
        'value' => 'Vnesite vrednost lastnosti',
        'info_url' => 'Vnesite neobvezno povezavo "več informacij"',
        'sort' => 'Vnesite vrstni red (nižje številke se prikažejo prve)',
    ],

    'help_text' => [
        'info_url' => 'Neobvezna povezava "več informacij"',
        'image' => 'Neobvezna slika za to vrednost (npr. logotip blagovne znamke)',
        'sort' => 'Nižje številke se prikažejo prve',
        'import_file' => 'Naložite Excel (.xlsx, .xls) ali CSV datoteko z dvema stolpcema: <strong>name</strong> in <strong>hex</strong>. Primer: Rdeča, #FF0000',
    ],

    'table' => [
        'columns' => [
            'value' => 'Vrednost',
            'image' => 'Slika',
            'info_url' => 'URL informacij',
            'sort' => 'Vrstni red',
            'products_count' => 'Proizvodi',
            'created_at' => 'Ustvarjeno',
        ],
        'filters' => [
            'property' => 'Lastnost',
        ],
        'actions' => [
            'edit' => 'Uredi',
            'delete' => 'Izbriši',
            'merge' => 'Združi…',
        ],
    ],

    'actions' => [
        'import' => 'Uvozi barve',
    ],

    'modal' => [
        'create_heading' => 'Ustvari vrednost lastnosti',
        'edit_heading' => 'Uredi vrednost lastnosti',
        'import_heading' => 'Uvozi vrednosti barv',
        'merge_heading' => 'Združi vrednost',
        'merge_from_label' => 'Združi vrednost…',
        'merge_to_label' => 'Z vrednostjo…*',
        'merge_helper' => 'To dejanje bo vsem izdelkom zamenjalo trenutno lastnost s to, označeno zgoraj. Nato bo trenutna lastnost izbrisana.',
        'merge_submit_label' => 'Združi',
        'cancel_label' => 'Prekliči',
        'merge_confirm_title' => 'Ste prepričani, da želite združiti?',
        'merge_confirm_body' => 'Vsi izdelki s trenutno vrednostjo bodo posodobljeni na izbrano vrednost. Dejanja ni mogoče razveljaviti.',
    ],

    'messages' => [
        'created' => 'Vrednost lastnosti je bila uspešno ustvarjena.',
        'updated' => 'Vrednost lastnosti je bila uspešno posodobljena.',
        'deleted' => 'Vrednost lastnosti je bila uspešno izbrisana.',
        'merged_title' => 'Vrednosti so združene',
        'merged_body' => 'Posodobili smo :affected izdelkov. Izbrana vrednost je ostala, druga je bila odstranjena.',
        'merged_error_title' => 'Združevanje ni uspelo',
        'merged_error_body' => 'Vrednosti trenutno ni mogoče združiti. Poskusite znova.',
    ],

    'pages' => [
        'title' => [
            'with_property' => 'Vrednosti za: :property',
            'default' => 'Vrednosti lastnosti',
        ],
        'breadcrumbs' => [
            'properties' => 'Lastnosti',
            'list' => 'Seznam',
        ],
    ],

    'notifications' => [
        'import_queued' => [
            'title' => 'Uvoz v čakalni vrsti',
            'body' => 'Uvoz barv je bil dodan v čakalno vrsto in bo obdelan v ozadju.',
        ],
        'import_completed' => [
            'title' => 'Uvoz končan',
            'body' => 'Uvoz končan: :inserted dodano, :skipped preskočeno, :errors napak.',
            'errors' => 'Napake',
        ],
        'import_failed' => [
            'title' => 'Uvoz ni uspel',
        ],
    ],
];
