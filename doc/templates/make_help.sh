#!/bin/sh

# This extracts all the help pages from the LCM website
# Files are seperated by language.

DOCROOT="http://www.lcm.ngo-bg.org/rubrique_help.php"

# You can also pass DEST from the command line
# DEST=/var/www/foo/inc/help ./make_help.sh
if [ "x$DEST" = "x" ]; then
	DEST="/var/www/legalcase/inc/help"
fi

if [ ! -d "$DEST" ]; then
	echo "$DEST: destination directory does not exist. Aborting."
	exit 1;
fi

if [ "X$1" = "X" -a "X$2" = "X" -a "X$3" = "X" ]; then
	wget -q -O /dev/stdout $DOCROOT | sort | grep http | xargs -n 3 ./make_help.sh
else
	mkdir -p "$DEST/$2"
	echo "$DEST/$2/$1.html"
	wget -q -O "$DEST/$2/$1.html" "$3" 
fi
