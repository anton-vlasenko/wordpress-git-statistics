<?php
const DS = DIRECTORY_SEPARATOR;
require_once __DIR__ . DS . 'boostrap.php';

if ( empty( $developers ) || empty( $repositories ) ) {
	throw new RuntimeException( 'config.php is invalid.' );
}

if ( empty( $argv[1] ) || empty( $argv[2] ) ) {
	throw new InvalidArgumentException( 'Invalid start or end date.' );
}

$timezone = new DateTimeZone( 'UTC' );
$start    = DateTimeImmutable::createFromFormat( 'Y-m-d', $argv[1], $timezone );
$start    = $start->setTime( 0, 0 );
$end      = DateTimeImmutable::createFromFormat( 'Y-m-d', $argv[2], $timezone );
$end      = $end->setTime( 0, 0 );

$statistics = fetch_statistics( $repositories, $developers, $start, $end );
echo sprintf("\nStatistics from %s to %s:\n", $start->format('Y-m-d'), $end->format('Y-m-d'));
print_statistics( $statistics );