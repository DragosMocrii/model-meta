<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Model Meta Morph Map
	|--------------------------------------------------------------------------
	|
	| Define a morph map to decouple your application's internal structure
	| from the database. This will help if you decide to extend your model
	| class, or change its name. If you don't define a morph map, then
	| the meta data will be tightly coupled to your model class names.
	|
	| Example:
	| 'morph_map' => [
	|   'posts' => App\Post::class,
    |   'videos' => App\Video::class,
	| ]
	|
	*/

	'morph_map' => [
		// define in here
	],

	/*
	|--------------------------------------------------------------------------
	| Preload all meta when fetching single meta
	|--------------------------------------------------------------------------
	|
	| Set to `true` to preload all meta when you only fetch a single meta. This
	| will prevent doing multiple DB queries if you fetch single meta multiple
	| times. Set to `false` to query the database every time you get a single
	| meta, unless it was already fetched before and cached.
	|
	*/

	'preload_on_get' => true,
];