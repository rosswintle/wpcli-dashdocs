<?php

$db = new sqlite3(__DIR__ . "/wpcli.docset/Contents/Resources/docSet.dsidx");
$db->query("DROP TABLE searchIndex");
$db->query("CREATE TABLE searchIndex(id INTEGER PRIMARY KEY, name TEXT, type TEXT, path TEXT)");
$db->query("CREATE UNIQUE INDEX anchor ON searchIndex (name, type, path)");

$newHead = <<<EOT
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head profile="http://gmpg.org/xfn/11">
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WP-CLI Commands | WordPress Developer Resources</title>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,400,300,600&amp;subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic' rel='stylesheet' type='text/css'>
<link rel='stylesheet' id='wp-dev-sass-compiled-css'  href='https://developer.wordpress.org/wp-content/themes/pub/wporg-developer/stylesheets/main.css?ver=20171008' type='text/css' media='all' />
</head>
EOT;

function reformatFile( $filename ) {
	global $newHead;
	$html = file_get_contents( $filename );
	$html = preg_replace('/<head.*<\/head>/s', $newHead, $html, 1);
	$html = preg_replace('/<div id="wporg-header".*<div id="content"/s', '<div class="devhub-wrap"><div id="content"', $html, 1);
	$html = preg_replace('/<\/div><!-- #page.*<\/body/s', '</div></body', $html, 1);
	$html = preg_replace('/<p><em>Command documentation is regenerated.*<\/em><\/p>/s', '<p><em><a href="https://wordpress.org/about/license/" title="WordPress is open source software">License / GPLv2</a></em></p>', $html, 1);
	$html = preg_replace('/<div class="github-tracker.*Create New Issue<\/a>\s*<\/div>/s', '', $html, 1);
 	file_put_contents( $filename, $html);
}

function processCommandFile( $commandPath ) {
	$filename = __DIR__ . '/' . $commandPath . '/index.html';
	reformatFile( $filename );
}

function processSubcommand( $subcommandPath, $parentCommandPath, $parentCommandName ) {
	global $db;
	echo "Processing sub-command: $subcommandPath\n";
	processCommandFile( $subcommandPath );
	$subcommandName = trim( str_replace($parentCommandPath, '', $subcommandPath), '/');
	$relativePath = sprintf('commands/%s/%s', $parentCommandName, $subcommandName);
	$query = sprintf('INSERT OR IGNORE INTO searchIndex(name, type, path) VALUES (\'%1$s %2$s\', \'Command\', \'%3$s/index.html\')', $parentCommandName, $subcommandName, $relativePath);
	$db->query( $query );
}

function processSubcommands( $parentCommandPath, $parentCommandName ) {
	$subcommands = glob( $parentCommandPath . "/*", GLOB_ONLYDIR );
	foreach( $subcommands as $subcommand ) {
		processSubcommand( $subcommand, $parentCommandPath, $parentCommandName );
	}
}

function processCommand( $commandPath ) {
	global $db;
	processCommandFile( $commandPath );
	$relativePath = str_replace('wpcli.docset/Contents/Resources/Documents/', '', $commandPath);
	$commandName = str_replace('commands/', '', $relativePath);
	echo "Processing $commandName\n";	
	$query = sprintf('INSERT OR IGNORE INTO searchIndex(name, type, path) VALUES (\'%1$s\', \'Command\', \'commands/%1$s/index.html\')', $commandName);
	$db->query( $query );

	processSubcommands( $commandPath, $commandName );
}

$mainfile = 'wpcli.docset/Contents/Resources/Documents/commands/commands.html';
reformatFile( $mainfile );

$commands = glob('wpcli.docset/Contents/Resources/Documents/commands/*', GLOB_ONLYDIR);
array_map( 'processCommand', $commands );
