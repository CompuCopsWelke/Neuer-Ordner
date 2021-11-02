<?php

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#index_post', 'url' => '/', 'verb' => 'POST'],

		['name' => 'editor#edit', 'url' => '/teil', 'verb' => 'GET' ],
		['name' => 'editor#create', 'url' => '/create_teil', 'verb' => 'GET' ],
		['name' => 'editor#delete', 'url' => '/delete_teil', 'verb' => 'GET' ],
		['name' => 'editor#update', 'url' => '/update_teil', 'verb' => 'POST' ]
	],
];
