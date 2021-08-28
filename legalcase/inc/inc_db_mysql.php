<?php

/*
	This file is part of the Legal Case Management System (LCM).
	(C) 2004-2006 Free Software Foundation, Inc.

	This program is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by the
	Free Software Foundation; either version 2 of the License, or (at your
	option) any later version.

	This program is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
	or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA  02111-1307, USA

	$Id$
*/

if (defined('_INC_DB_MYSQL')) return;
define('_INC_DB_MYSQL', '1');

if (! function_exists("mysqli_query"))
	die("ERROR: MySQL is not correctly installed. Verify that the php-mysql
	module is installed and that the php.ini has something similar to
	'extension=mysql.so'. Refer to the user's manual FAQ for more information.");

//
// SQL query functions
//

function lcm_sql_server_info() {
	return "MySQL"; // @mysqli_get_server_info();
}

function lcm_mysql_set_utf8() {
	global $lcm_mysql_link;

	mysqli_query($lcm_mysql_link, 'SET NAMES utf8');
	mysqli_query($lcm_mysql_link, "SET CHARACTER SET UTF8");
	mysqli_query($lcm_mysql_link, "SET SESSION CHARACTER_SET_SERVER = UTF8");

	// And yet more overkill, because I am having problems with MySQL 4.1.9
	mysqli_query($lcm_mysql_link, "SET CHARACTER_SET_RESULTS = UTF8");
	mysqli_query($lcm_mysql_link, "SET CHARACTER_SET_CONNECTION = UTF8");
	mysqli_query($lcm_mysql_link, "SET SESSION CHARACTER_SET_DATABASE = UTF8");
	mysqli_query($lcm_mysql_link, "SET SESSION collation_connection = utf8_general_ci");
	mysqli_query($lcm_mysql_link, "SET SESSION collation_database = utf8_general_ci");
	mysqli_query($lcm_mysql_link, "SET SESSION collation_server = utf8_general_ci");
}

function lcm_query_db($query, $accept_fail = false) {
	global $lcm_mysql_link;
	static $tt = 0;

	$my_debug   = $GLOBALS['sql_debug'];
	$my_profile = $GLOBALS['sql_profile'];

	/* [ML] I have no idea whether this is overkill, but without it,
	   we get strange problems with Cyrillic and other non-latin charsets.
	   We need to check whether tables were installed correctly, or else
	   it will not show non-latin utf8 characters correctly. (i.e. for
	   people who upgraded LCM, but didn't import/export their data to 
	   fix the tables.)
	   @todo 2021 Update: this seems completely unnecessary, but there may be users
	   upgrading from very old versions.
	*/
	if (read_meta('db_utf8') == 'yes') {
		lcm_mysql_set_utf8();
	} elseif ((! read_meta('db_utf8') == 'no') && (! read_meta('lcm_db_version'))) {
		// We are not yet installed, so check MySQL version on every request
		// Note: checking is is_file('inc/data/inc_meta_cache.php') is not
		// enough, because the keywords cache may have been generated, but not
		// the meta.
		lcm_mysql_set_utf8();
	}

	$query = process_query($query);

	if ($my_profile)
		$m1 = microtime();

	$result = mysqli_query($lcm_mysql_link, $query);

	if ($my_debug AND $my_profile) {
		$m2 = microtime();
		list($usec, $sec) = explode(" ", $m1);
		list($usec2, $sec2) = explode(" ", $m2);
		$dt = $sec2 + $usec2 - $sec - $usec;
		$tt += $dt;
		echo "<small>".htmlentities($query);
		echo " -> <font color='blue'>".sprintf("%3f", $dt)."</font> ($tt)</small><p>\n";
	}

	if ($my_debug)
		lcm_debug("QUERY: $query\n", 1, 'sql');

	if (lcm_sql_errno() && (!$accept_fail)) {
		$s = lcm_sql_error();
		$error = _T('warning_sql_query_failed') . "<br />\n" . htmlentities($query) . "<br />\n";
		$error .= "&laquo; " . htmlentities($s) . " &raquo;<br />";
		lcm_panic($error);
	}

	return $result;
}

function lcm_query_restore_table($query) {
	$ver = @mysql_get_server_info();

	if (preg_match("/^CREATE TABLE/", $query)) {
		// Remove possible ENGINE=MyISAM, CHARSET=latin1, etc. at end of query
		$query = preg_replace("/\) (ENGINE=|TYPE=)[^\)]*/", ")", $query);

		//
		// Clean table structure, for backups from MySQL 4.x imported into MySQL 5.x
		//
		
		// MySQL 5.1: foo_field datetime DEFAULT '0000-00-00 00:00:00' NOT NULL -> not allowed
		// so transform into: foo_field datetime NOT NULL. Default values are bad anyway for datetime.
		$query = preg_replace("/default '0000-00-00 00:00:00'/i", " ", $query);

		// Remove 'FULLTEXT KEY' entries...
		$query = preg_replace("/FULLTEXT KEY `?\w+`? \(`?\w+`?\),?/", " ", $query);

		// Sometimes, the previous statement ends the query with: "foo_statement,)"
		$query = preg_replace("/,\s*\)/m", " )", $query);


		// Activate UTF-8 only if using MySQL >= 4.1
		// (regexp excludes MySQL <= 4.0, easier for forward compatibility)
		if (! preg_match("/^(4\.0|3\.)/", $ver)) {
			$query .= " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ";

			// [ML] SHOULD BE DONE IN inc_db_upgrade.php since lcm_meta might not exist yet!
			// For those wondering why.. LCM <= 0.6.3 didn't correctly create
			// tables using "character set utf8". We still need to be somehow
			// backwards compatible, so we use the lcm_meta hack to activate
			// whether lcm_query() should "set session character_set_server = utf8"
			// if ($restore) {
			//	write_meta('db_utf8', 'yes');
			//	write_metas();
			// }
		}
	}

	return lcm_query($query);
}

function lcm_query_create_table($table, $fields, $keys = array()) {
	// $ver = @mysql_get_server_info();
	$new_fields = array();

	foreach ($fields as $f) {
		$tmp = $f;

		// MySQL 5.1: foo_field datetime DEFAULT '0000-00-00 00:00:00' NOT NULL -> not allowed
		// so transform into: foo_field datetime NULL. Default values are bad anyway for datetime.
		$tmp = preg_replace("/default '0000-00-00 00:00:00' not null/i", "default null", $tmp);

		$new_fields[] = $tmp;
	}

	$query = "CREATE TABLE $table ("
		. implode(", ", $new_fields);

	if (count($keys)) {
		$query .= ', ';
		$new_keys = array();

		foreach ($keys as $name => $field)
			$new_keys[] = "KEY $name ($field)";
		
		$query .= implode(', ', $new_keys);
	}

	$query .= ")";

	// Activate UTF-8 (requires MySQL >= 4.1)
	$query .= " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ";

	// accept query fail because of following scenario:
	// - user exports database in LCM 0.7.0
	// - user goes to other server, installs LCM 0.7.1
	// - user imports the database
	// - LCM launches upgrade procedure
	// - Result: there may be new tables in 0.7.1, and the upgrade procedure
	//   will freak out when trying to create those tables.
	$accept_fail = (isset($GLOBALS['debug']) && $GLOBALS['debug'] ? false : true);
	return lcm_query($query, $accept_fail);
}

function lcm_query_create_unique_index($table, $idx_name, $field) {
	lcm_query("CREATE UNIQUE INDEX $idx_name ON $table ($field)");
}


//
// Process a standard query
// This includes the "prefix" name for the database tables
//
function process_query($query) {
	$db = '';
	$suite = '';

	if ($GLOBALS['mysql_recall_link'] AND $db = $GLOBALS['lcm_mysql_db'])
		$db = '`'.$db.'`.';

	// change the names of the tables ($table_prefix)
	// for example, lcm_case may become foo_case
	if ($GLOBALS['flag_pcre']) {
		if (preg_match('/\s(VALUES|WHERE)\s/i', $query, $regs)) {
			$suite = strstr($query, $regs[0]);
			$query = substr($query, 0, -strlen($suite));
		}
		$query = preg_replace('/([,\s])lcm_/', '\1'.$db.$GLOBALS['table_prefix'].'_', $query) . $suite;
	}
	else {
		if (eregi('[[:space:]](VALUES|WHERE)[[:space:]]', $query, $regs)) {
			$suite = strstr($query, $regs[0]);
			$query = substr($query, 0, -strlen($suite));
		}
		$query = ereg_replace('([[:space:],])lcm_', '\1'.$db.$GLOBALS['table_prefix'].'_', $query) . $suite;
	}

	return $query;
}


//
// Connection to the database
//

function lcm_connect_db($host, $port, $login, $pass, $db = 0, $link = 0) {
	global $lcm_mysql_link, $lcm_mysql_db;	// for multiple connections
	global $debug;

	if (! $login)
		lcm_panic("missing login?");

	if ($link && $db)
		return mysql_select_db($db);

	if ($port > 0) $host = "$host:$port";
	$lcm_mysql_link = @mysqli_connect($host, $login, $pass);

	// if ($debug)
	//	mysql_query("SET SESSION sql_mode='STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO'");

	if ($debug)
		mysqli_query("SET SESSION sql_mode='STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
		

	if ($lcm_mysql_link && $db) {
		$lcm_mysql_db = $db;
		return @mysqli_select_db($lcm_mysql_link, $db);
	} else {
		return $lcm_mysql_link;
	}
}

// Note: the $db is not use (used for pgsql)
function lcm_connect_db_test($host, $login, $pass, $db = '', $port = 0) {
	unset($link);

	// Non-silent connect, should be shown in <!-- --> anyway
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	if ($port > 0) $host = "$host:$port";
	$link = mysqli_connect($host, $login, $pass);

	if ($link) {
//		mysql_close($link);
		$link = null;
		return true;
	} else {
		return false;
	}
}

function lcm_list_databases($host, $login, $pass, $port = 0) {
	$databases = array();

	if ($port > 0) $host = "$host:$port";
	$link = @mysqli_connect($host, $login, $pass);

	if ($link) {
		$result = mysqli_query($link, 'SHOW DATABASES');

		if ($result AND (($num = mysqli_num_rows($result)) > 0)) {
			for ($i = 0; $i < $num; $i++) {
				$name = mysqli_fetch_array($result);
				if ($name['Database'] != 'test' && $name['Database'] != 'information_schema') {
					$databases[] = $name['Database'];
				}
			}
		}

		return $databases;
	} else {
		echo "<!-- NO LINK -->\n";
		return NULL;
	}
}


//
// Fetch the results
//

function lcm_fetch_array($r) {
	if ($r)
		return mysqli_fetch_array($r);
}

function lcm_fetch_assoc($r) {
	if ($r)
		return mysqli_fetch_assoc($r);
}

function spip_fetch_array($r) {
	lcm_log("use of deprecated function: spip_fetch_array, use lcm_fetch_array instead");
	return lcm_fetch_array($r);
}

function lcm_fetch_object($r) {
	if ($r)
		return mysql_fetch_object($r);
}

function spip_fetch_object($r) {
	lcm_log("use of deprecated function: spip_fetch_object, use lcm_fetch_object instead");
	return lcm_fetch_object($r);
}

function lcm_fetch_row($r) {
	if ($r)
		return mysqli_fetch_row($r);
}

function spip_fetch_row($r) {
	lcm_log("use of deprecated function: spip_fetch_row, use lcm_fetch_row instead");
	return lcm_fetch_row($r);
}

function lcm_sql_error() {
	global $lcm_mysql_link;
	if ($lcm_mysql_link) {
		return mysqli_error($lcm_mysql_link);
	}
	return null;
}

function lcm_sql_errno() {
	global $lcm_mysql_link;
	if ($lcm_mysql_link) {
		return mysqli_errno($lcm_mysql_link);
	}
	return null;
}

function lcm_num_rows($r) {
	if ($r)
		return mysqli_num_rows($r);
}

function spip_num_rows($r) {
	lcm_log("use of deprecated function: spip_num_rows, use lcm_num_rows instead");
	return lcm_num_rows($r);
}

function lcm_data_seek($r,$n) {
	if ($r)
		return mysqli_data_seek($r,$n);
}

function lcm_free_result($r) {
	if ($r)
		return mysqli_free_result($r);
}

function spip_free_result($r) {
	lcm_log("use of deprecated function: spip_free_result, use lcm_free_result instead");
	return lcm_free_result($r);
}

function lcm_insert_id($name, $field) {
	global $lcm_mysql_link;
	// note: name and field are used only by pgsql
	return mysqli_insert_id($lcm_mysql_link);
}

function lcm_query_date_add_interval($date, $op, $type, $units) {
	$ret = "";

	$type = strtoupper($type);

	switch ($op) {
		case '+':
			// ex: DATE_ADD('2000-01-01', INTERVAL 1 MONTH)
			$ret = "DATE_ADD('$date', INTERVAL $units $type)";
			break;
		case '-':
			// ex: DATE_SUB('2000-01-01', INTERVAL 1 MONTH)
			$ret = "DATE_SUB('$date', INTERVAL $units $type)";
			break;
		default:
			lcm_panic("Operand unknown");
	}

	return $ret;
}

// Make sure to put $date in quotes, ex: '2000-01-01 00:00:00'
// we don't put by default, because it is made to also accept fields
// ex: DATE_FORMAT(t.date_start, '...')
function lcm_query_trunc_field($date, $type) {
	$ret = "";

	switch ($type) {
		case 'day':
			$ret = "DATE_FORMAT($date, '%Y-%m-%d')";
			break;
		case 'year':
			$ret = "YEAR($date)";
			break;
		default:
			lcm_panic("Not supported");
	}

	return $ret;
}

function lcm_query_sum_time($field_start, $field_end) {
	return "sum("
		. "IF(UNIX_TIMESTAMP($field_end) > 0,"
			. "UNIX_TIMESTAMP($field_end)-UNIX_TIMESTAMP($field_start),"
			. "0)"
		. ") ";
}

function lcm_query_subst_time($field_start, $field_end) {
	return "IF(UNIX_TIMESTAMP($field_end) > 0, UNIX_TIMESTAMP($field_end) - UNIX_TIMESTAMP($field_start), 0)";
}

// Put a local lock on a given LCM installation
// [ML] we can probably ignore this
function spip_get_lock($nom, $timeout = 0) {
	global $lcm_mysql_db, $table_prefix;
	if ($table_prefix) $nom = "$table_prefix:$nom";
	if ($lcm_mysql_db) $nom = "$lcm_mysql_db:$nom";

	$nom = addslashes($nom);
	list($lock_ok) = spip_fetch_array(spip_query("SELECT GET_LOCK('$nom', $timeout)"));
	return $lock_ok;
}

function spip_release_lock($nom) {
	global $lcm_mysql_db, $table_prefix;
	if ($table_prefix) $nom = "$table_prefix:$nom";
	if ($lcm_mysql_db) $nom = "$lcm_mysql_db:$nom";

	$nom = addslashes($nom);
	spip_query("SELECT RELEASE_LOCK('$nom')");
}

?>
