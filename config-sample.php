<?php
$developers = [
	[
		'name'                  => 'John Doe',
		'commits_search_regexp' => '(John Doe|johndoe|doejohn)',
	],
	[
		'name'                  => 'Sarah Connor',
		'commits_search_regexp' => '(Sarah Connor|sarahconnor|conorsarah)',
	],
];

$repositories = [
	'wordpress-develop' => [
		'url'     => 'https://github.com/WordPress/wordpress-develop.git',
		'remotes' => [
			'johndoe'     => 'https://github.com/johndoe/wordpress-develop.git',
			'sarahconnor' => 'https://github.com/sarahconnor/wordpress-develop.git',
		]
	],
	'gutenberg'         => [
		'url' => 'https://github.com/WordPress/gutenberg.git',
	]
];