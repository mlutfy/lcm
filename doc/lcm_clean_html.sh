#!/bin/sh

LANG=$1
SRC="lcm_manual_$LANG"
DEST=$2 # ex /var/www/legalcase/inc/help/

cd $SRC

for i in *_*.html; do
	tidy -i -utf8 -wrap 300 -q -asxhtml $i | \
	# remove most start tags in margin (<!DOCTYPE, <body, etc.)
	egrep -v "^<(\w|\!D)" | \
	# remove most end tags, except <textarea>
	grep -v "^</[^(text|pre)]" | \
	# remove other tags which were in body
	grep -v "DTD/xhtml1" | \
	grep -v "<meta name=" | \
	grep -v "<meta http-equiv=" | \
	grep -v "<link rel=" | \
	grep -v xhtml1-strict | \
	grep -v "<title>" |  \
	grep -v "<h1>" | \
	grep -v "<strong>Subsections</strong>" | \
	sed "s/\"\`/\&bdquo;/g" | \
	sed "s/\"'/\&ldquo;/g" \
	 > "$DEST/$LANG/$i"
done

