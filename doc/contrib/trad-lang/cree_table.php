<?

$home = "..";
chdir($home);
include("ecrire/inc_version.php3");
# include("./trad-lang/inc_connect.php3");
include("ecrire/inc_connect.php3");

$res = mysql_query("
 CREATE TABLE trad_lang (
  id varchar(128) NOT NULL default '',
  module varchar(128) NOT NULL default '',
  lang varchar(16) NOT NULL default '',
  str text NOT NULL,
  comm text NOT NULL,
  ts timestamp(14) NOT NULL,
  status varchar(16) default NULL,
  traducteur varchar(32) default NULL,
  md5 varchar(32) default NULL,
  orig tinyint(4) NOT NULL default '0',
  date_modif datetime default NULL
) TYPE=MyISAM;");

if ($res == false)
{
  echo "impossible de creer la table : ".mysql_error()."\n";
  exit(1);
}

$res = mysql_query("create index idx_tl on trad_lang(id);");
$res = mysql_query("create index idx_t2 on trad_lang(module);");
$res = mysql_query("create index idx_t3 on trad_lang(id,module,lang);");
$res = mysql_query("create index idx_t4 on trad_lang(module,lang);");

echo "table cree\n";
exit(0);

?>
