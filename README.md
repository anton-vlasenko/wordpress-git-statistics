# WordPress Git statistics

This script aims to provide `git log` based metrics (number of commits, number of insertions/deletions) for all developers defined in the config file.

It also generates statistics for Core Committers' patches.

### Getting started

1. `git clone https://github.com/anton-vlasenko/wordpress-git-statistics.git`
2. `cd wordpress-git-statistics`
3. `cp config-sample.php config.php`
4. Edit `config.php` and adjust it to your needs.
5. `php parse.php <start-date> <end-date>`
6. The script will output `Git` statistics between `<start-date>` and `<end-date>` for each developer defined in the `$developers` array. 

### Example
- `php parse.php 2022-12-30 2022-12-31` - parses commits that were created between `2022-12-30 00:00:00` and `2022-12-31 00:00:00`.

### Notes
1. The timezone is always `UTC`.
2. The script always downloads new objects and refs from the repositories defined in `$repositories` (please see `config.php)`.
3. Repositories are cloned at the first launch only. Therefore, the more repositories you need to parse, the longer it will take to clone them.