<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Transforme une couleur hexa en vectorielle R,G,B
 *
 * @param string $couleur
 * @return string
 */
function geol_couleur_hex_to_dec($couleur) {
	include_spip("inc/filtres_images_mini");
	$couleur = couleur_html_to_hex($couleur);
	$couleur = preg_replace(",^#,","",$couleur);
	$retour["red"] = hexdec(substr($couleur, 0, 2));
	$retour["green"] = hexdec(substr($couleur, 2, 2));
	$retour["blue"] = hexdec(substr($couleur, 4, 2));
	
	return implode(',',$retour);
}

/**
 * Définition du id_collection du plugin collections s'il n'est pas dispo
 */
if (!defined('_DIR_PLUGIN_COLLECTIONS')){
	function critere_id_collection_dist($idb, &$boucles, $crit){}
}
?>