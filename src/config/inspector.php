<?php

return [
		
	'collectors' => [
		
		'ExceptionCollector' => ['inspector'=>true, 'fullscreen'=>true],
		'DBCollector' 		=> ['inspector'=>true, 'fullscreen'=>true],
		'ServerCollector' 	=> ['inspector'=>true, 'fullscreen'=>true],
		'SessionCollector' 	=> ['inspector'=>true, 'fullscreen'=>true],
		'ResponseCollector' => ['inspector'=>true, 'fullscreen'=>true],
		'RequestCollector'	=>['inspector'=>true, 'fullscreen'=>true],
		'RoutesCollector'	=>['inspector'=>false, 'fullscreen'=>true],
		'TimerCollector'	=>['inspector'=>true, 'fullscreen'=>true],

	],

	'hide_server_keys' => ['HTTP_X_XSRF_TOKEN', 'APP_KEY', 'DB_HOST', 'DB_PASSWORD', 'DB_USERNAME', 'HTTP_COOKIE', 'MAIL_PASSWORD', 'REDIS_PASSWORD'],
	'exception_render' => true,

];