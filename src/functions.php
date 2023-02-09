<?php
function parse_commits( string $author, DateTimeInterface $start, DateTimeInterface $end ): array {
	$command = "git log --all -E -i --pretty=full --shortstat --since='{$start->format('c')}' --until='{$end->format('c')}' --date=iso-strict --author='$author'";
	$commits = implode( '', run_command( $command ) );

	return preg_split( '/commit [a-h\d]{40}Author/', $commits, - 1, PREG_SPLIT_NO_EMPTY );
}

function parse_commit_statistics( array $commits ): array {
	$insertions                    = 0;
	$deletions                     = 0;
	$core_committer_merges         = 0;
	$regular_commits               = 0;
	$core_committer_lines_reviewed = 0;

	foreach ( $commits as $commit ) {
		$is_core_committer_merge = 1 === preg_match( '/git-svn-id: http/', $commit );
		$is_core_committer_merge ? ++ $core_committer_merges : ++ $regular_commits;

		$matches = [];
		if ( preg_match( '/(\d+) insertions?\(\+\)/', $commit, $matches ) ) {
			if ( $is_core_committer_merge ) {
				$core_committer_lines_reviewed += (int) $matches[1];
			} else {
				$insertions += (int) $matches[1];
			}

		}

		$matches = [];
		if ( preg_match( '/(\d+) deletions?\(-\)/', $commit, $matches ) ) {
			if ( $is_core_committer_merge ) {
				$core_committer_lines_reviewed += (int) $matches[1];
			} else {
				$deletions += (int) $matches[1];
			}
		}
	}

	return [
		'commit(s)'                => $regular_commits,
		'insertion(s)'             => $insertions,
		'deletion(s)'              => $deletions,
		'WordPress Core patch(es)' => $core_committer_merges,
		'core_patches_diff_lines'  => $core_committer_lines_reviewed,
	];
}

function run_command( string $command ): array {
	$verbose = ! defined( 'VERBOSE' ) || VERBOSE;
	if ( $verbose ) {
		echo sprintf( "Directory: '%s', command: %s.\n", getcwd(), $command );
	}

	$debug = defined( 'DEBUG' ) && DEBUG;
	if ( ! $debug ) {
		$command = $command . ' 2>&1';
	}

	$output = [];
	$code   = 0;
	exec( $command, $output, $code );
	if ( 0 !== $code ) {
		throw new RuntimeException( sprintf( 'An error occurred while executing this command: "%s".', $command ) );
	}

	return $output;
}

function change_directory( string $directory ): void {
	if ( false === chdir( $directory ) ) {
		throw new RuntimeException( 'Cannot change the working directory to "%s"', $directory );
	}
}

function fetch_statistics( array $repositories, array $developers, DateTimeInterface $start, DateTimeInterface $end ): array {
	$statistics     = [];
	$root_directory = getcwd();
	if ( ! is_dir( 'repositories' ) ) {
		// This is more convenient than mkdir().
		run_command( "mkdir repositories" );
	}

	foreach ( $repositories as $repository_handle => $repository ) {
		change_directory( 'repositories' );
		$git_directory_path = getcwd() . DS . $repository_handle . DS . '.git';
		if ( is_dir( $git_directory_path ) ) {
			change_directory( $repository_handle );
		} else {
			run_command( sprintf( 'git clone %s %s', $repository['url'], $repository_handle ) );
			change_directory( $repository_handle );
			if ( ! empty( $repository['remotes'] ) ) {
				foreach ( $repository['remotes'] as $remote_repository_name => $remote_repository_url ) {
					run_command( sprintf( 'git remote add %s %s', $remote_repository_name, $remote_repository_url ) );
				}
			}
		}

		run_command( 'git fetch --all' );
		foreach ( $developers as $developer ) {
			$commits              = parse_commits( $developer['commits_search_regexp'], $start, $end );
			$developer_statistics = parse_commit_statistics( $commits );
			if ( ! isset( $statistics[ $developer['name'] ] ) ) {
				$statistics[ $developer['name'] ] = $developer_statistics;
			} else {
				foreach ( $developer_statistics as $metric => $value ) {
					$statistics[ $developer['name'] ][ $metric ] += $value;
				}
			}
		}

		change_directory( $root_directory );
	}

	return $statistics;
}

function print_statistics( array $statistics ): void {
	foreach ( $statistics as $developer_name => $developer_statistics ) {
		$result               = [];
		$developer_statistics = array_filter( $developer_statistics );
		if ( ! $developer_statistics ) {
			continue;
		}
		foreach ( $developer_statistics as $metric => $value ) {
			if ( 'core_patches_diff_lines' === $metric ) {
				continue;
			}
			if ( 'WordPress Core patch(es)' === $metric ) {
				$result[] = sprintf( '%s %s consisting of %s changed line(s)', $value, $metric, $developer_statistics['core_patches_diff_lines'] );
			} else {
				$result[] = sprintf( '%s %s', $value, $metric );
			}

		}

		if ( $result ) {
			echo sprintf( "%s: %s\n", $developer_name, implode( ', ', $result ) );
		}
	}
}
