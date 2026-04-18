<?php

declare(strict_types=1);

return [
	'routes' => [
		['name' => 'admin#index', 'url' => '/admin', 'verb' => 'GET'],
		['name' => 'admin#saveApiKey', 'url' => '/admin/apikey', 'verb' => 'POST'],
		['name' => 'admin#sendNow', 'url' => '/admin/send-now', 'verb' => 'POST'],
	],
];
