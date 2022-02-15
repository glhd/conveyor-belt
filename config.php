<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Collect Exceptions
	|--------------------------------------------------------------------------
	|
	| By default, Conveyor Belt will throw an exception as soon as it's
	| triggered unless you set the `$collect_exceptions` property to `true` on
	| your Artisan command. You can change the default behavior here.
	|
	*/
	
	'collect_exceptions' => false,
	
	/*
	|--------------------------------------------------------------------------
	| Chunk Size
	|--------------------------------------------------------------------------
	|
	| By default, Conveyor Belt will run your queries in 1000 record chunks.
	| You can change the default chunk size here.
	|
	*/
	
	'chunk_count' => 1000,
	
	/*
	|--------------------------------------------------------------------------
	| Progress Bar Format
	|--------------------------------------------------------------------------
	|
	| The default Conveyor Belt progress bar will show a detailed summary
	| of your command's progress. You can update the format used here.
	|
	| See: https://symfony.com/doc/current/components/console/helpers/progressbar.html#custom-formats
	|
	*/
	
	'progress_formats' => [
		'base' => '%bar% %current% %message%',
		'base_with_memory' => '%bar% %current% (%memory%) %message%',
		'count' => '%bar% %current%/%max% (~%remaining%) %message%',
		'count_with_memory' => '%bar% %current%/%max% (%memory%, ~%remaining%) %message%',
	],
];
