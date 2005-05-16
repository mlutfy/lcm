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
	sed "s/\"'/\&ldquo;/g" | \
	# transform sections to h3, instead of h2 (used at top title)
	sed "s/h2/h3/g" | \
	# transform target links references to remove file name
	sed "s/href=\".*\.html#/href=\"#/g" | \
	# move target links to <h3><a ...></a> text </h3>, avoids visual mess
	sed "s/<h3>\(<a .*\">\)\(.*\)<\/a><\/h3>/<h3>\1<\/a>\2<\/h3>/" > "$DEST/$LANG/$i"
done

