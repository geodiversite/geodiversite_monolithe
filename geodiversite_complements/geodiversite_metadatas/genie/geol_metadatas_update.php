<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function genie_geol_metadatas_update_dist($t) {
	// nombre de documents traités par iteration
	$nb_docs = @define('_GEOL_METADATAS_NB_DOCS',5);
	$extensions = array('jpg');
	if ($documents = sql_select("*", "spip_documents", sql_in("extensions", $extensions) ." AND geol_metadatas = 'non'", "", "maj", "0,".intval($nb_docs+1))) {
		while($nb_docs-- AND $row = sql_fetch($documents)) {
			include_spip('inc/distant');
			include_spip('inc/documents');
			// le fichier existe-t-il/est-il accessible ?
			if (!$fichier = copie_locale(get_spip_doc($row['fichier']), 'test')) {
				// le fichier n'est pas accessible, on log mais on poursuit pour les autres
				spip_log('Pas de copie locale de '.$row['fichier'], "geol_metadatas");
				// et on met le statut en erreur
				sql_updateq("spip_documents", array('geol_metadatas' => 'err'), "id_document=".intval($row['id_document']));
			}else{
				// le fichier existe, on update
				
			}
		}
		if ($row = sql_fetch($documents)) {
			spip_log("il reste des docs a indexer...", "geol_metadatas");
			return 0-$t; // il y a encore des docs a indexer
		}
	}
	return 0;
}

?>