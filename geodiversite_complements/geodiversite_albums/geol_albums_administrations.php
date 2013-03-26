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
			if ($id_collection > 0) {
				objet_modifier('collection', $id_collection, $set);
				objet_instituer('collection', $id_collection, array('statut' => 'publie'));
				
				// copie des liens de grappes_liens vers collections_liens pour les articles
				$articles = sql_allfetsel('*','spip_grappes_liens',"objet = 'article' AND id_grappe = " . $grappe['id_grappe']);
				foreach($articles as $article) {
					objet_associer(array('collection' => $id_collection), array($article['objet'] => $article['id_objet']), array('rang' => $article['rang']));
				}
				
				// associer l'auteur id_admin de la grappe à la collection
				objet_associer(array('auteur' => $grappe['id_admin']), array('collection' => $id_collection));
				
				// copie des liens de grappes_liens vers auteurs_liens pour les auteurs
				$auteurs = sql_allfetsel('*','spip_grappes_liens',"objet = 'auteur' AND id_grappe = " . $grappe['id_grappe']);
				foreach($auteurs as $auteur) {
					objet_associer(array($auteur['objet'] => $auteur['id_objet']), array('collection' => $id_collection));
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
}

/**
 * Fonction de désinstallation du plugin.
 * 
 * Supprime la meta d'installation du plugin
 * 
 * @param string $nom_meta_base_version
 * 		Le nom de la meta d'installation
 */
function geol_albums_vider_tables($nom_meta_base_version) {
	effacer_meta($nom_meta_base_version);
}

?>