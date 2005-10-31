#!/usr/bin/perl -w

# 	This file is part of the Legal Case Management System (LCM).
# 	(C) 2004-2005 Free Software Foundation, Inc.
# 
# 	This program is free software; you can redistribute it and/or modify it
# 	under the terms of the GNU General Public License as published by the 
# 	Free Software Foundation; either version 2 of the License, or (at your 
# 	option) any later version.
# 
# 	This program is distributed in the hope that it will be useful, but 
# 	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
# 	or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
# 	for more details.
# 
# 	You should have received a copy of the GNU General Public License along 
# 	with this program; if not, write to the Free Software Foundation, Inc.,
#   59 Temple Place, Suite 330, Boston, MA  02111-1307, USA

use strict;
use Test::More qw( no_plan );

my @expected = (14, 819, 833);

my $url = $ARGV[0] . "/run_rep.php?export=csv&rep=4";
my $id_session = $ARGV[1];

print $id_session, "\n", $url, "\n";

my @output = `wget -O - --no-cookies --header "Cookie: lcm_session=$id_session" "$url"`;
my $cpt = 0;

for my $line (@output) {
	next if ($cpt++ == 0);

	my @items = split(/,/, $line);
	if ($items[1] =~ m/(\d+)/) {
		is($1, shift(@expected), $items[0] . " -> $1");
	}
	
	$cpt++;
}


