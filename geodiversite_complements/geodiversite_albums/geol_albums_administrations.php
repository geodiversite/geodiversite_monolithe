<?php

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Fonction d'installation et de mise à jour du plugin.
 * 
 * Effectue une migration des albums basés sur les grappes vers les tables du plugin media_collections
 * 
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 * @param float $version_cible
 * 		Le numéro de version vers laquelle mettre à jour
 */
function geol_albums_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();
	$maj['create'] = array(array('geol_albums_init'));
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Migration des albums grappes vers media_collections
 * 
 * Appelée lors de l'installation du plugin
 */
function geol_albums_init(){
	if ($grappes = sql_allfetsel('*','spip_grappes', sql_in('type', array('album_perso', 'album_coop', 'balade')))) {
		
		include_spip('action/editer_objet');
		include_spip('action/editer_liens');

		foreach($grappes as $grappe) {
			// récupérer les infos des anciens albums (grappes)
			$set = array(
				'id_admin' => $grappe['id_admin'],
				'titre' => $grappe['titre'],
				'descriptif' => $grappe['descriptif'],
				'date' => $grappe['date']
			);
			
			if ($grappe['type'] == 'album_perso')
				$set['type_collection'] = 'perso';
			elseif ($grappe['type'] == 'album_coop')
				$set['type_collection'] = 'coop';
			elseif ($grappe['type'] == 'balade')
				$set['type_collection'] = 'balade';
			
			// créer des collections
			$id_collection = objet_inserer('collection');
			objet_modifier('collection', $id_collection, $set);
			objet_instituer('collection', $id_collection, array('statut' => 'publie'));
			
			// copie des liens des grappes vers les collections
			$liens = sql_allfetsel('*','spip_grappes_liens','id_grappe = ' . $grappe['id_grappe']);
			
			foreach($liens as $lien) {
				$association = objet_associer(array('collection' => $id_collection), array($lien['objet'] => $lien['id_objet']), array('rang' => $lien['rang']));
			}
			
			// maj des liens des forums attachés aux grappes
			$forums = sql_allfetsel('id_forum','spip_forum',"objet = 'grappe' AND id_objet = ".$grappe['id_grappe']);
			foreach($forums as $forum) {
				sql_updateq('spip_forum', array('objet' => 'collection', 'id_objet' => $id_collection), 'id_forum = ' . $forum['id_forum']);
			}
			
			// maj des liens des points gis attachés aux grappes
			$points = sql_allfetsel('id_gis','spip_gis_liens',"objet = 'grappe' AND id_objet = ".$grappe['id_grappe']);
			foreach($points as $point) {
				sql_updateq('spip_gis_liens', array('objet' => 'collection', 'id_objet' => $id_collection), 'id_gis = ' . $point['id_gis']);
			}
		}
	}
}

?>