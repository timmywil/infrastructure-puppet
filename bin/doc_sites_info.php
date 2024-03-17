<?php

$yaml = file( __DIR__ . '/../hieradata/common.yaml' );

$sites = null;
$current = null;

foreach ( $yaml as $line ) {
	// Top-level key
	if ( preg_match( '/^(\S+):$/', $line, $m ) ) {
		if ( $m[1] === 'docs_sites' ) {
			// Start of docs_sites
			$sites = [];
		} elseif ( $sites !== null ) {
			// End of docs_sites
			break;
		}
		// Keep searching
		continue;
	}

	// First-level key
	if ( $sites === null ) {
		// Ignore nested data under other keys
		continue;
	}
	if ( preg_match( '/^  (\S+):$/', $line, $m ) ) {
		$sites[$m[1]] = [];
		$current =& $sites[$m[1]];
		$current['key'] = $m[1];
		continue;
	}

	// Second- and third-level key
	if ( preg_match( '/^    (?:  )?(\S+): "?(\S+)"?$/', $line, $m ) ) {
		switch ($m[1]) {
		case 'host':
		case 'path':
		// repository.name
		case 'name':
		// repository.branch
		case 'branch':
		// repository.tag_format
		case 'tag_format':
			$current[$m[1]] ??= $m[2];
			break;
		}
	}
}

// Generate markdown
print "<!-- Generated by /bin/doc_sites_info.php and configured by docs_sites in /hieradata/common.yaml -->\n\n";
print sprintf( "| %-40s | %-14s | %s\n", 'URL', 'Deployment', 'Staging' );
print sprintf( "| %'-40s | %'-14s | %'-40s\n", '', '', '' );
foreach ( $sites as $site ) {
	$branch = $site['branch'] ?? 'main';
	$deploy = @$site['tag_format'] === '*semver_tag' ? 'semver tags' : "`$branch` branch";
	$link = @$site['name'] ? "https://github.com/{$site['name']}/tree/$branch/" : null;
	print sprintf( "| %-40s | %-14s | %s\n",
		'https://' . $site['host'] . ( $site['path'] ?? '/' ),
		$link ? "[$deploy]($link)" : $deploy,
		'https://stage.' . $site['host'] . ( $site['path'] ?? '/' )
	);
}
print "\n<!-- END -->\n";
