#!/bin/sh

# This extracts all the help pages from the LCM website
# Files are seperated by language.

DOCROOT="http://www.lcm.ngo-bg.org/rubrique_help.php"

if [ "X$1" = "X" -a "X$2" = "X" -a "X$3" = "X" ]; then
	wget -q -O /dev/stdout $DOCROOT | sort | grep http | xargs -n 3 ./make_help.sh
else
	mkdir -p $2
	wget -q -O "./$2/$1.html" "$3" 
fi
