<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');

function geol_upgrade($nom_meta_base_version,$version_cible){

	$maj = array();
	$maj['create'] = array(
		array('geol_installation'),
	);
	$maj['0.2'] = array(
		array('geol_upgrade_02'),
	);
	$maj['0.2.1'] = array(
		array('geol_upgrade_021'),
	);
	$maj['0.2.2'] = array(
		array('geol_upgrade_022'),
	);

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);

}

function geol_installation(){

	// activer l'inscription des visiteurs
	if (lire_config('accepter_inscriptions') == 'non') ecrire_config('accepter_inscriptions', 'oui');
	
	// taille des vignettes à 300px
	ecrire_config('taille_preview', '300');
	
	// thème bootstrap pour les box
	ecrire_config('mediabox/skin', 'bootstrap');
	
	// publication des articles post-datés
	ecrire_config('post_dates', 'oui');
	
	// forcer l'utilisation des mots clés
	if (lire_config('articles_mots') == 'non') ecrire_config('articles_mots', 'oui');
		
	// activer les docs sur les articles
	is_array($documents_objets = explode(',',lire_config('documents_objets'))) || $documents_objets = array();
	if (!in_array('spip_articles', $documents_objets)){
		ecrire_config('documents_objets', implode(',',array('spip_articles','')));
	}
	
	// pas de titre, lien et barre typo dans les forums
	if (lire_config('forums_titre') == 'oui') ecrire_config('forums_titre', 'non');
	if (lire_config('forums_afficher_barre') == 'oui') ecrire_config('forums_afficher_barre', 'non');
	if (lire_config('forums_urlref') == 'oui') ecrire_config('forums_urlref', 'non');
	
	// configuration de GIS : activer le geocoder + geolocalisation sur les articles
	ecrire_config('gis/geocoder','on');
	ecrire_config('gis/gis_objets',array('spip_articles','spip_documents'));
	
	// configuration d'Emballe médias
	ecrire_config('emballe_medias/fichiers/chercher_article','on');
	ecrire_config('emballe_medias/fichiers/fichiers_images',array('gif','jpg','png'));
	ecrire_config('emballe_medias/fichiers/fichiers_audios',array('mp3'));
	ecrire_config('emballe_medias/fichiers/fichiers_videos',array('flv','mp4'));
	ecrire_config('emballe_medias/fichiers/fichiers_textes',array('kml'));
	
	// configuration de socialtags
	ecrire_config('socialtags/jsselector','#socialtags');
	
	// création du groupe de mots clés echelle et de ses mots clés
	$Terreur = array();
	if (sql_countsel('spip_groupes_mots', "titre = 'echelle'") == 0) {

		$id_groupe = sql_insertq('spip_groupes_mots', array(
			'titre'=> 'echelle',
			'descriptif'=> '',
			'tables_liees' => 'articles',
			'unseul' => 'oui',
			'obligatoire' => 'non',
			'minirezo' => 'oui',
			'comite' => 'oui',
			'forum' => 'non')
		);
		if (sql_error() != '') die((_T('geol:erreur_install_mots ')).sql_error());
		
		$mots_echelle = array(
						'10. 10 km',
						'20. 1 km',
						'30. 500 m',
						'40. 100 m',
						'50. 50 m',
						'60. 10 m',
						'70. 1 m',
						'80. 50 cm',
						'90. 10 cm',
						'100. 5 cm',
						'110. 1 cm',
						'120. 5 mm',
						'130. 1 mm',
						'140. 0,1 mm'
		);
		
		foreach ($mots_echelle as $echelle) {
		  sql_insertq('spip_mots', 
					  array('titre'=>$echelle, 'id_groupe'=>$id_groupe, 'type'=>'echelle')
					 );
		  if (sql_error() != '') $Terreurs[] = (_T('erreur_creation_mot_cle')).$echelle.': '.sql_error();
		}
	}
	
	if (count($Terreurs) != 0) echo implode('<br>',$Terreurs);
	
	// creation des menus du squelette
	include_spip('inc/filtres');
	$plugin = chercher_filtre('info_plugin');
	if ($plugin('menus','est_actif')){
		include_spip('action/editer_menu');
		$menus = array(
			array('titre'=>'A propos', 'identifiant'=>'pied_apropos', 'css'=>''),
			array('titre'=>'Explorer', 'identifiant'=>'pied_explorer', 'css'=>''),
			array('titre'=>'Univers', 'identifiant'=>'pied_univers', 'css'=>''),
			array('titre'=>'Univers', 'identifiant'=>'entete_univers', 'css'=>'univers')
		);
		foreach ($menus as $menu) {
			if(!sql_getfetsel('id_menu','spip_menus','identifiant='.sql_quote($menu['identifiant']))){
				$id_menu = insert_menu();
				$err = menu_set($id_menu, $menu);
			}
		}
	}

	// maj suivantes
	geol_upgrade_02();
	geol_upgrade_021();
	geol_upgrade_022();

}

function geol_upgrade_02(){
	include_spip('action/editer_diogene');
	$secteur_medias = lire_config('geol/secteur_medias',1);
	if(!$id_diogene_medias = sql_getfetsel('id_diogene','spip_diogenes','objet="emballe_media" AND id_secteur = '.intval($secteur_medias))){
		$id_diogene_medias = diogene_inserer();
		$set_media = array(
			'titre' => _T('geol:publier_media'),
			'description' => '',
			'champs_caches' => '',
			'champs_ajoutes' => array(
				'geo','mots','licence'
			),
			'menu'=> '',
			'statut_auteur' => '1comite',
			'statut_auteur_publier' => '1comite'

		);
		$err = diogene_modifier($id_diogene_medias, $set_media);
		$err = diogene_instituer($id_diogene_medias, array(
			'id_secteur' => $secteur_medias,
			'objet' => 'emballe_media',
			'type' => 'article'
			)
		);
	}
	ecrire_config('emballe_medias/fichiers/publier_dans_secteur','on');
}

function geol_upgrade_021(){
	ecrire_config('nuage/score_min',0);
}

function geol_upgrade_022(){
	ecrire_config('notifications/forum_article','on');
}

function geol_vider_tables($nom_meta_base_version){
	effacer_meta($nom_meta_base_version);
}

?>