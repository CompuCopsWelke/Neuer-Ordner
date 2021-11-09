<?php

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#index_post', 'url' => '/', 'verb' => 'POST'],

		['name' => 'editor#edit', 'url' => '/teil', 'verb' => 'GET' ],
		['name' => 'editor#create', 'url' => '/create_teil', 'verb' => 'GET' ],
		['name' => 'editor#delete', 'url' => '/delete_teil', 'verb' => 'POST' ],
        ['name' => 'editor#update', 'url' => '/update_teil', 'verb' => 'POST' ],

        ['name' => 'editor#add_doc', 'url' => '/add_doc_bestand', 'verb' => 'POST' ],
        ['name' => 'editor#show_doc', 'url' => '/show_doc_bestand', 'verb' => 'GET' ],
        ['name' => 'editor#del_doc', 'url' => '/del_doc_bestand', 'verb' => 'GET' ]
	],
];
