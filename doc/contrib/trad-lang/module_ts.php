<?


// nom module
$nom_module = "Interface Traduction";

// petit nom du module
$nom_mod = "ts"; 

// Langue a traduire par defaut
$lang_mere = 'en';

// fonction d'export fichiers
$export_function = "exporter_ts";


// --------------------------------
// donnees propres aux modules spip
// --------------------------------

// repertoires de travail
$dir_lang = "ecrire/lang";   
$dir_bak = "ecrire/lang/bak";

// Prefixe des fichiers de langue
$lang_prefix = 'ts_';
$lang_suffix = '.php3';

// Debut du fichier de langue
$lang_prolog = "<"."?php\n\n// This is a SPIP language file  --  Ceci est un fichier langue de SPIP\n\n";

// Fin du fichier de langue
$lang_epilog = "\n\n?".">\n";


if (defined("MODULE_TS"))
  return;
define("MODULE_TS", "1");


// doit retourner true ou false
// le second argument doit etre passe en ref.
// et est initialise par la fonction
function exporter_ts($lang_cible, $nomfic, $telech=0)
{
  global $left,$right;
  global $dir_lang, $lang_prefix, $lang_suffix;
  global $lang_prolog, $var_mod, $lang_epilog;

  $fic_nom = $lang_prefix.$lang_cible.$lang_suffix;
  $fic_exp = $dir_lang."/".$fic_nom;

  $tab = array();
  $conflit = array();  // A CHANGER
  lire_lang($lang_cible, &$tab);

  ksort($tab);
  reset($tab);
  $initiale = "";
  $texte = $lang_prolog;
  $texte .= "\$GLOBALS[\$GLOBALS['idx_lang']] = array(\n";

  while (list($code, $chaine) = each($tab))
    {
      if (!array_key_exists($code, $conflit))
        {
          if ($initiale != strtoupper($code[0]))
            {
              $initiale = strtoupper($code[0]);
              $texte .= "\n\n// $initiale\n";
            }                                                                                                                                              
	  $texte .= "'".$code."' => '".texte_script($chaine)."',\n";
        }
    }

  // ecriture des chaines en conflit
  if (count($conflit))
    {
      ksort($conflit);
      reset($conflit);
      $texte .= "\n\n// PLUS_UTILISE\n";
      while (list($code, $chaine) = each($conflit))
        $texte .= "'".$code."' => '".texte_script($chaine)."',\n";
    }

  $texte = ereg_replace (",\n$", "\n\n);\n", $texte);
  $texte .= $lang_epilog;

  if ($telech == 1)
    {
       header("content-type: text/plain");
       header("Content-Disposition: attachment; filename=".$fic_nom);
       flush();
       echo $texte;
       exit;
    }
  else
    {
      $nomfic=$fic_exp;
      $f = @fopen($fic_exp, "wb");
      if (!$f)
        return false;
      fwrite($f, $texte);
      fclose($f);
      @chmod($fic_exp, 0666);
    }
  
  return true;
}


?>
