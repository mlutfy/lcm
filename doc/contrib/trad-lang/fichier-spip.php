<?

// fichier-spip.php
// sert a importer les fichiers spip dans la base
// de donnees "trad-lang".

// le fichier est accede a travers son module 
// (le fichier module est passe en parametre)

// le script commence par tester si le fichier
// a deja ete importe dans la base. Si oui, il
// propose soit d'ecraser les chaines existantes, 
// soit de les conserver. Les chaines qui
// n'existent pas dans la base sont de toute
// facon crees.

// le troisieme parametre a passer au script est
// la langue d'origine. Le fichier de cette
// langue pour le module doit deja avoir ete
// importe.

// fichier-spip.php <module> <langue> <langue_origine>


$dir_modules = "./trad-lang";


function test_args($argc, $argv)
{
  global $in_module, $in_langue, $dir_modules;
  global $dir_module, $in_origine, $in_nmod;
  
  $usage = "%s <module> <langue> <langue_origine>
  - module : le module a importer.
  - langue : la langue a importer
  - langue_origine : langue origine pour ce module\n\n";

  if ($argc != 4)
    {
      echo sprintf($usage, $argv[0]);
      return 0;
    }

  $in_nmod = $argv[1];
  $in_module = $dir_modules."/module_".$in_nmod.".php";
  $in_langue = $argv[2];
  $in_origine = $argv[3];

  if (!@file($in_module))
    {
      echo "module inexistant : ".$in_module."\n";
      return 0;
    }

  return 1;
}


function test_module()
{
  global $in_module, $in_langue, $in_nmod;

  // teste si le fichier a deja ete
  // importe
  
  include($in_module);

  // teste le fichier langue
  $ficl = $dir_lang."/".$lang_prefix.$in_langue.$lang_suffix;
  if (!@file($ficl))
    {
      echo "fichier langue <".$ficl."> inexistant\n";
      return 0;
    }

  $res = mysql_query("select id from trad_lang where module='".$in_nmod.
		     "' and lang='".$in_langue."'");
  $nb = mysql_num_rows($res);
  if ($nb > 0)
    return 1;
  else
    return 0;
}


// retourne la valeur du champ dans
// la langue origine
function get_val_orig($id)
{
  global $nom_mod, $lang_mere;

  $ret = "";
  $quer = "SELECT str FROM trad_lang WHERE module='".$nom_mod."' AND lang='".$lang_mere."'".
    " AND id='".$id."'";
  $res = mysql_query($quer);  
  if ($res)
    {
      $row = mysql_fetch_assoc($res);
      $ret = $row['str'];
    }
  return $ret;
}


function renseigne_base($chaines, $choix)
{
  global $in_module, $in_langue, $in_origine;
  global $lang_mere, $in_nmod;

  $orig = 0;
  if ($in_langue == $in_origine)
    $orig = 1;

  reset($chaines);
  while(list($id, $str) = each($chaines))
    {
      $md5 = ""; $dummy="";

      if (!$orig)
	$md5 = md5(get_val_orig($id));
      $quer = "select id from trad_lang where module='".$in_nmod."' and ".
	"lang ='".$in_langue."' and id='".$id."'";
      $res = mysql_query($quer);
      $nb = mysql_num_rows($res);

      if ($nb)
	{
	  if ($choix == "A")
	    {
	      if (!$orig)
		$dummy = "md5 = '".$md5."',";

	      $quer = "update trad_lang set ".
		"id = '".$id."',".
		"module = '".$in_nmod."', ".
		"str = '".mysql_escape_string($str)."', ".$dummy.
		"lang = '".$in_langue."', ".
		"orig=".$orig.",status=''".
		" where module = '".$in_nmod."' and id = ".
		"'".$id."' and lang = '".$in_langue."'";
		//echo $quer."\n";
	    }
	}
      else
	{
	  if (!$orig)
	    $quer = "insert into trad_lang (id, module, str, lang, orig,md5, status) ".
	      "values ('".$id."', '".$in_nmod."', '".
	      mysql_escape_string($str)."', '".$in_langue."', ".$orig.", '".$md5."', '') ";
	  else
	    $quer = "insert into trad_lang (id, module, str, lang, orig, status) ".
	      "values ('".$id."', '".$in_nmod."', '".
	      mysql_escape_string($str)."', '".$in_langue."', ".$orig.", '') ";
	}

      $res = mysql_query($quer);      

      if ($res == false) 
        echo "ECHEC REQUETE : ".$quer."\n";
    }
}



// main
{
  $home = "..";
  chdir($home);
  include("ecrire/inc_version.php3");
  include("./ecrire/inc_connect.php3");
//  include("./trad-lang/inc_connect.php3");

  if (!test_args($argc, $argv))
    exit(1);

  $choix = "A";
  if (test_module() == 1)
    {
      echo "Le fichier langue ".$in_langue." pour ce module a deja ete importe dans la base.\n";
      echo "Voulez vous :\n";
      echo " - ecraser les chaines deja existantes avec les nouvelles valeurs ? [A]\n";
      echo " - importer seulement les chaines non existantes, ne pas ecraser ? [B] \n";
      echo "Choix ?";
      
      $stdin = fopen('php://stdin', 'r');
      $choix = fgetc($stdin);
      fclose($stdin);

      if ($choix != "A" && $choix != "B")
	{
	  echo "reponse erronee\n";
	  exit(1);
	}
    }
    
  include($in_module);
  $ficl = $dir_lang."/".$lang_prefix.$in_langue.$lang_suffix;
  include($ficl);
  echo "traitement du fichier ".$ficl."\n";

  $str_lang = $GLOBALS[$GLOBALS['idx_lang']];
  renseigne_base(&$str_lang, $choix);
  exit(0);
}

?>
