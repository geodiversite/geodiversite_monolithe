<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Insertion dans le pipeline taches_generales_cron
 *
 * Vérifie la présence à intervalle régulier de fichiers à mettre à jour
 * 
 * @param array $taches_generales Un array des tâches du cron de SPIP
 * @return L'array des taches complété
 */
function geol_metadatas_taches_generales_cron($taches_generales) {
	
	@define('_GEOL_METADATAS_INTERVALLE_CRON',120); // toutes les 2 minutes
	$taches_generales['geol_metadatas_update'] = _GEOL_METADATAS_INTERVALLE_CRON;
	
	return $taches_generales;
}

?>