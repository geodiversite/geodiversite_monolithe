<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Compile le critère {geol_albums #ID_AUTEUR}
 *
 * Permet de sélectionner les albums de l'id_auteur passé en paramètre
 * 
 * @param string $idb     Identifiant de la boucle
 * @param array $boucles  AST du squelette
 * @param Critere $crit   Paramètres du critère dans cette boucle
 * @return void
 */
function critere_geol_albums_dist($idb,&$boucles,$crit){
	$boucle = &$boucles[$idb];
    $id_table = $boucle->id_table;
	$primary = $boucles[$idb]->primary;
	$table_objet = table_objet_sql($primary);
	
	// Récupérer l'id_auteur passé en paramètre
	if (isset($crit->param[0])) {
		$id_auteur = calculer_liste($crit->param[0], array(), $boucles, $boucles[$idb]->id_parent);
	} else {
		// Pas de paramètre => erreur
		return (array('zbug_critere_necessite_parametre', array('critere' => $crit->op )));
	}
	
	// Préparer nos deux critères en mode OR et non AND
	// 1) Albums persos dont l'auteur est admin
	// 2) Albums coops dont l'auteur est membre
	$where = "array('OR',
		array('=', '$id_table.id_admin', $id_auteur),
		". geol_albums_albums_critere_where($primary,$id_table,$table_objet,$id_auteur) ."
	)";
	// Le critère est conditionnel, test sur id_auteur
	$boucle->where[] = "intval($id_auteur) ? $where :''";
}

function geol_albums_albums_critere_where($primary,$id_table,$table_objet,$id_auteur){
	$in = "sql_in('$primary', prepare_geol_albums($id_auteur), '')";
	return "array('IN','$primary','('.sql_get_select('bbbb.$primary','$table_objet as bbbb',$in,'','','','',\$connect).')')";
}

/**
 * Fonction de préparation du critère {geol_albums #ID_AUTEUR}
 * 
 * Retourne les albums coop d'un auteur pour les inclure dans la boucle
 * 
 * @param string $id_auteur L'id de l'auteur
 * @param string $server Le serveur
 */
function prepare_geol_albums($id_auteur,$server=''){
	$albums_auteur = sql_select('id_grappe','spip_grappes_liens','objet="auteur" AND id_objet='.intval($id_auteur));
	$objet = $objets = array();
	while($objet = sql_fetch($albums_auteur)){
		$objets[] = $objet['id_grappe'];
	}
	return $objets;
}

?>