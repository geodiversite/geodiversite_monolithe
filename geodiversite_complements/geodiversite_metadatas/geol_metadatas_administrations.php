<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Fonction d'installation et de mise à jour du plugin.
 * 
 * Ajoute le champ sur la table spip_documents
 * 
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 * @param float $version_cible
 * 		Le numéro de version vers laquelle mettre à jour
 */
function geol_metadatas_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();
	$maj['create'] = array(
		array('sql_alter',"TABLE spip_documents ADD geol_metadatas VARCHAR(3) NOT NULL default 'non'"),
	);
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Fonction de désinstallation du plugin.
 * 
 * Supprime le champ sur la table spip_documents et la meta d'installation du plugin
 * 
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 */
function geol_metadatas_vider_tables($nom_meta_base_version) {
	sql_alter("TABLE spip_documents DROP geol_metadatas");
	effacer_meta($nom_meta_base_version);
}

?>