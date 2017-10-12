# WP-CLI Dash Docs

## Grabbing the HTML

From the Contents/Resources/Documents directory:

`wget -E -r --level=2 -k -p -nH --cut-dirs=2 --accept-regex="cli\/commands" https://developer.wordpress.org/cli/commands`

I usually copy this into DocumentsOriginal and then:

`rm -r wpcli.docset/Contents/Resources/Documents/*`
`cp -r DocumentsOriginal/* wpcli.docset/Contents/Resources/Documents`

before processing.


## Generating the database

From this dir, run:

`php generate.php`

This script:
* reformats the HTML a little (based on current structure at October 2017 - this may break!)
* creates a new SQLite DB (drops the searchIndex table)
* adds the WP-CLI commands and sub-commands to the SQLite DB

 ## Issues/todos

 * It would be good if we could take the CSS offline in an automated way.
 * Dashdocs lets you specify [online redirection](https://kapeli.com/docsets#onlineRedirection), but each file here has index.html in the URL which 404's when you redirect to the inline equivalent using the DashDocSetFallbackURL key in Info.plist.  Would be good to add the "Online page at" comments for each page.
