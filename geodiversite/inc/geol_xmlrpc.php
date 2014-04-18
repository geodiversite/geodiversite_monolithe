<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Récupère la liste des médias
 * 
 * Arguments possibles :
 * -* login string
 * -* pass string
 * -* id_rubrique int
 * -* id_auteur int
 * -* recherche string
 * -* where array() : conditions à ajouter dans la clause where du select
 * -* champs_demandes array (champs que l'on souhaite récupérer, séparés par une virgule, sinon, on retourne l'ensemble)
 * -* tri array : array('!date') soit par date inversée, du dernier au premier, par défaut.
 * 		 si on veut trier sur les champs des auteurs mettre auteurs.champs comme auteurs.nom pour le tri sur le nom des auteurs
 * -** Si distance dans le tri
 * -*** lat float
 * -*** lon float
 * -* limite
 */
function geodiv_liste_medias($args) {
	global $spip_xmlrpc_serveur;
	$objet = 'article';
	$table_objet = 'articles';
	$secteur_medias = lire_config('geol/secteur_medias',1);

	$what[] = $table_objet.'.id_article';
	$from = 'spip_articles as articles LEFT JOIN spip_auteurs_liens AS auteurs ON '.$table_objet.'.id_article=auteurs.id_objet and auteurs.objet="article"';
	$where = is_array($args['where']) ? $args['where'] : array();
	$where[] = $table_objet.'.id_secteur='.intval($secteur_medias);
	$order = is_array($args['tri']) ? $args['tri'] : array('!date');

	if(intval($args['id_auteur'])){
		$where[] = 'auteurs.id_auteur='.intval($args['id_auteur']);
	}

	if(is_array($GLOBALS['visiteur_session'])){
		if(!$args['id_auteur'])
			$where[] = 'auteurs.id_auteur='.intval($GLOBALS['visiteur_session']['id_auteur']);
		else
			$where[] = $table_objet.'.statut="publie"';
	}else
		$where[] = $table_objet.'.statut="publie"';

	/**
	 * Distance dans le tri
	 * On a besoin de savoir par rapport à quoi donc :
	 * -* $args['lon'] la longitude est obligatoire
	 * -* $args['lat'] la latitude est obligatoire
	 * -* on force l'existance d'un point gis (les medias non géolocalisés n'apparaitront donc jamais)
	 */
	if(in_array('distance',$order) OR in_array('!distance',$order)){
		$lat = $args['lat'];
		$lon = $args['lon'];
		if(!is_numeric($lon) OR !is_numeric($lat)){
			$erreur = _T('gis:erreur_xmlrpc_lat_lon');
			return new IXR_Error(-32601, attribut_html($erreur));
		}else{
			$what[] = "(6371 * acos( cos( radians(\"$lat\") ) * cos( radians( gis.lat ) ) * cos( radians( gis.lon ) - radians(\"$lon\") ) + sin( radians(\"$lat\") ) * sin( radians( gis.lat ) ) ) ) AS distance";
			$from .= ' LEFT JOIN spip_gis_liens as lien ON '.$table_objet.'.id_article=lien.id_objet AND lien.objet="article" LEFT JOIN spip_gis as gis ON gis.id_gis=lien.id_gis';
			$where[] = 'gis.id_gis > 0';
		}
	}

	/**
	 * Une recherche
	 */	
	if(is_string($args['recherche']) AND strlen($args['recherche']) > 3){
		$prepare_recherche = charger_fonction('prepare_recherche', 'inc');
		list($rech_select, $rech_where) = $prepare_recherche($args['recherche'], $table_objet, $where);
		$what[] = $rech_select;
		$from .= ' INNER JOIN spip_resultats AS resultats ON ( resultats.id = '.$table_objet.'.id_article ) ';
		$where[] = $rech_where;
	}
	
	$medias_struct = array();
	if($medias = sql_select($what,$from,$where,'',$order,$args['limite'])){
		while($media = sql_fetch($medias)){
			$struct=array();
			$args['id_article'] = $media['id_article'];
			/**
			 * On utilise la fonction geodiv_lire_media pour éviter de dupliquer trop de code
			 */
			$struct = geodiv_lire_media($args);
			$medias_struct[] = $struct;
		}
	}
	
	return $medias_struct;
}

/**
 * Récupère le contenu d'un média
 * 
 * Arguments possibles :
 * -* login
 * -* pass
 * -* id_article int (Obligatoire)
 * -* champs_demandes string (champs que l'on souhaite récupérer, séparés par une virgule, sinon, on retourne l'ensemble)
 * -* document_largeur int (largeur maximale du document, si c'est une image, défaut largeur du document original)
 * -* document_hauteur int (hauteur maximale du document, si c'est une image, défaut hauteur du document original)
 * -* vignette_format string (carre ou autre, autre n'a pas de fonction)
 * -* vignette_largeur int (largeur de la vignette en px, défaut 100)
 * -* vignette_hauteur int (hauteur de la vignette en px, défaut 100)
 */
function geodiv_lire_media($args){
	global $spip_xmlrpc_serveur;
	
	if(!intval($args['id_article']) > 0){
		$erreur = _T('xmlrpc:erreur_identifiant',array('objet'=>'article'));
		return new IXR_Error(-32601, attribut_html($erreur));
	}

	$champs_demandes = is_array($args['champs_demandes']) ? $args['champs_demandes'] : array(); 
	$format_vignette = $args['vignette_format'];
	
	$config = lire_config('geol',array());
	$secteur_medias = (intval($config['secteur_medias']) > 0) ? $config['secteur_medias'] : 1;
	$args_media = array_merge($args,array('objet'=>'article','id_objet'=>$args['id_article']));
	$res = $spip_xmlrpc_serveur->read($args_media);

	if(!$res)
		return $spip_xmlrpc_serveur->error;
	
	$id_secteur = $res['result'][0]['id_secteur'] ? $res['result'][0]['id_secteur'] : sql_getfetsel('id_secteur','spip_articles','id_article='.intval($args['id_article']));
	/**
	 * Sécurité : L'article demandé n'est pas un média
	 */
	if($id_secteur != $secteur_medias){
		$erreur = _T('xmlrpc_geodiv:erreur_article_media',array('id_article'=>$args['id_article']));
		return new IXR_Error(-32601, attribut_html($erreur));
	}

	/**
	 * Si on demande précisément certains champs, on ne fait que les renvoyer
	 */
	if(count($champs_demandes) != 0){
		foreach ($res['result'][0] as $champ => $valeur){
			if(!in_array($champ,array('id_article')) && !in_array($champ,$champs_demandes))
				unset($res['result'][0][$champ]);
		}
	}
	
	/**
	 * On ajoute le booléen "modifiable" :
	 * Uniquement si on ne demande pas de champs spécifique ou qu'il soit dedans
	 */
	if((count($champs_demandes) == 0) || in_array('modifiable',$champs_demandes)){
		if(autoriser('modifier','id_article',$args['id_article'],$GLOBALS['visiteur_session']))
			$res['result'][0]['modifiable'] = 1;
		else
			$res['result'][0]['modifiable'] = 0;
	}
	
	/**
	 * On ajoute le logo de l'article :
	 * Uniquement si on ne demande pas de champs spécifique ou qu'il soit dedans
	 */
	if((count($champs_demandes) == 0) || in_array('logo',$champs_demandes)){
		$logo = quete_logo('id_article','on', $res['result'][0]['id_article'], '', false);
		if(is_array($logo))
			$res['result'][0]['logo'] = url_absolue($logo[0]);
	}
	
	/**
	 * On a les infos de l'article, on récupère maintenant :
	 * (si pas de champs demandés spécifiés ou les champs en question sont demandés)
	 * -* Son document
	 * -* Sa vignette
	 * -* Sa géoloc
	 * -* Ses mots clés
	 * -** tags
	 * -** échelle
	 * -* Ses commentaires
	 */
	
	/**
	 * On commence par le document principal
	 */
	if((count($champs_demandes) == 0) || in_array('document',$champs_demandes) || in_array('vignette',$champs_demandes)){
		$document = sql_fetsel('*','spip_documents as documents LEFT JOIN spip_documents_liens AS lien ON documents.id_document=lien.id_document','lien.objet='.sql_quote('article').' AND lien.id_objet='.intval($args['id_article']),array(),array(),1);
		if(is_array($document)){
			include_spip('inc/documents');
			include_spip('inc/filtres_images_mini');
			include_spip('filtres/images_transforme');
			
			if((count($champs_demandes) == 0) || in_array('document',$champs_demandes)){
				$largeur_document = $args['document_largeur'];
				$hauteur_document = $args['document_hauteur'];
				if(in_array($document['extension'], array('gif','png','jpg')) && ($largeur_document || $hauteur_document)){
					$res['result'][0]['document'] = url_absolue(extraire_attribut(image_reduire(get_spip_doc($document['fichier']),$largeur_document,$hauteur_document),'src'));
				}else{
					$res['result'][0]['document'] = url_absolue(get_spip_doc($document['fichier']));
				}
				$res['result'][0]['media'] = $document['media'];
				$res['result'][0]['extension'] = $document['extension'];
			}
			
			if((count($champs_demandes) == 0) || in_array('vignette',$champs_demandes)){
				$largeur_vignette = $args['vignette_largeur'] ? $args['vignette_largeur'] : 100;
				$hauteur_vignette = $args['vignette_hauteur'] ? $args['vignette_hauteur'] : 100;
				if($format_vignette == 'carre'){
					$vignette = extraire_attribut(quete_logo_document($document, $lien, $align, $mode_logo, '', '', $connect=NULL),'src');
					$res['result'][0]['vignette'] = url_absolue(extraire_attribut(image_recadre(image_passe_partout($vignette,$largeur_vignette,$hauteur_vignette),$largeur_vignette,$hauteur_vignette),'src'));
				}else{
					$vignette = liens_absolus(quete_logo_document($document, $lien, $align, $mode_logo, $largeur_vignette, $hauteur_vignette, $connect=NULL));
					$res['result'][0]['vignette'] = extraire_attribut($vignette,'src');
				}
			}
		}
	}

	/**
	 * On ajoute les auteurs
	 * On met juste leur id_auteur + nom, si besoin de plus une autre requête sur l'auteur est à effectuer 
	 */
	if((count($champs_demandes) == 0) || in_array('auteurs',$champs_demandes)){
	$auteurs = sql_select('auteurs.nom, auteurs.id_auteur','spip_auteurs AS auteurs INNER JOIN spip_auteurs_liens AS L1 ON L1.id_auteur = auteurs.id_auteur INNER JOIN spip_articles AS L2 ON L2.id_article = L1.id_objet',"L1.objet='article' AND auteurs.statut != '5poubelle' AND L2.id_article = ".intval($res['result'][0]['id_article']));
		while($auteur=sql_fetch($auteurs)){
			$res['result'][0]['auteurs'][] = $auteur;
		}
	}
	
	/**
	 * On ajoute les points de géoloc
	 */
	if(defined('_DIR_PLUGIN_GIS') && (count($champs_demandes) == 0) || in_array('gis',$champs_demandes)){
		include_spip('gis_xmlrpc','inc');
		$tous_gis = sql_select('gis.id_gis','spip_gis AS `gis` INNER JOIN spip_gis_liens AS L1 ON L1.id_gis = gis.id_gis','L1.id_objet = '.intval($args['id_article']).' AND (L1.objet = '.sql_quote('article').')');
		while($gis=sql_fetch($tous_gis)){
			$args['id_gis'] = $gis['id_gis'];
			$res['result'][0]['gis'][] = spip_lire_gis($args);
		}
	}
	
	/**
	 * On ajoute les tags
	 * On met juste leur id_mot + titr, si besoin de plus une autre requête sur le mot est à effectuer 
	 */
	if((count($champs_demandes) == 0) || in_array('tags',$champs_demandes)){
		$tags_group = (intval($config['groupe_tags']) > 0) ? intval($config['groupe_tags']) : intval(lire_config('spipicious/groupe_mot'));
		if($tags_group > 0){
			$tous_tags = sql_select('mots.id_mot, mots.titre','spip_mots AS `mots` INNER JOIN spip_mots_liens AS L1 ON ( L1.id_mot = mots.id_mot )','L1.id_objet = '.intval($args['id_article']).' AND (L1.objet = "article") AND (mots.id_groupe = '.$tags_group.')');
			while($tag=sql_fetch($tous_tags)){
				$res['result'][0]['tags'][] = $tag;
			}
		}
	}
	
	/**
	 * Et on ajoute l'échelle
	 * On met juste son id_mot + titre, si besoin de plus une autre requête sur le mot est à effectuer 
	 */
	if((count($champs_demandes) == 0) || in_array('echelle',$champs_demandes)){
		$echelle_group = (intval($config['groupe_echelle']) > 0) ? intval($config['groupe_echelle']) : 0;
		if($echelle_group > 0){
			$echelle = sql_fetsel('mots.id_mot, mots.titre','spip_mots AS `mots` INNER JOIN spip_mots_liens AS L1 ON ( L1.id_mot = mots.id_mot )','L1.id_objet = '.intval($args['id_article']).' AND (L1.objet = "article") AND (mots.id_groupe = '.$echelle_group.')');
			if(is_array($echelle)){
				$res['result'][0]['echelle'][] = $echelle;
			}
		}
	}
	
	/**
	 * Et on ajoute les forums
	 * On n'affiche que les forums publiés (statut publie)
	 * 
	 * On met juste :
	 * -* id_forum
	 * -* id_thread 
	 * -* titre
	 * -* auteur
	 * -* id_auteur
	 * Si besoin de plus une autre requête sur le forum est à effectuer 
	 */
	if((count($champs_demandes) == 0) || in_array('forums',$champs_demandes)){
		$forums = sql_select('id_forum, id_thread,titre,auteur,id_auteur','spip_forum','objet='.sql_quote('article').' AND id_objet = '.intval($args['id_article']).' AND (statut = '.sql_quote('publie').')');
		while($forum=sql_fetch($forums)){
			$res['result'][0]['forums'][] = $forum;
		}
	}
	$media_struct = $res['result'][0];
	$media_struct = array_filter($media_struct);
	return $media_struct;
}

/**
 * Crée un Média
 * 
 * Arguments possibles :
 * -* login string Le login de l'utilisateur (Obligatoire)
 * -* pass string Le mot de passe de l'utilisateur (Obligatoire)
 * -* id_rubrique int La rubrique du média
 * -* document
 * -* em_type
 * -* texte
 * -* titre
 * -* chapo
 * -* ps
 * -* id_licence
 * -* statut
 * -* gis
 * -** 'lat', 'lon', 'zoom', 'titre', 'descriptif', 'adresse', 'code_postal', 'ville', 'region', 'pays'
 * -* tags
 * 
 * return array Toutes les informations du media créé
 */
function geodiv_creer_media($args){
	global $spip_xmlrpc_serveur;
	/**
	 * On est obligé d'être identifié
	 */
	if(!is_array($GLOBALS['visiteur_session'])){
		$erreur = _T('xmlrpc:erreur_mauvaise_identification');
		return new IXR_Error(-32601, attribut_html($erreur));
	}
	
	/**
	 * On est obligé d'avoir un document avec :
	 * -* bits
	 * -* name
	 * -* type
	 */
	if((strlen($args['document']['bits']) == 0) OR !$args['document']['name']){
		$erreur = _T('geol:erreur_fichier_inconnu');
		spip_log('on plante, pas assez d infos','xmlrpc');
		return new IXR_Error(-32601, attribut_html($erreur));
	}else{
		$tmp_name = _DIR_VAR.$args['document']['name'];
		spip_log(strlen($args['document']['bits']),'xmlrpc');
		if($fichier_64 = base64_decode($args['document']['bits'])){
			spip_log('On a un fichieren base_64','xmlrpc');
		}
	}
	
	/**
	 * On enregistre le document temporaire sur le serveur dans local/
	 * 
	 */
	if ($f = fopen($tmp_name, 'w+')) {
		fwrite($f, $fichier_64 ? $fichier_64 : $args['document']['bits']);
		fclose($f);
	}else{
		$erreur = _T('geol:erreur_fichier_inconnu');
		spip_log($erreur,'xmlrpc');
		return new IXR_Error(-32601, attribut_html($erreur));
	}
	
	$args['document']['tmp_name'] = $tmp_name;
	unset($args['document']['bits']);
	
	$args_media = array('objet'=>'media','id_objet'=>'','set'=>$args);
	$media = $spip_xmlrpc_serveur->create($args_media);
	if(!$media){
		return $spip_xmlrpc_serveur->error;
	}
	else{
		$args_media['id_article'] = $media['result']['id'];
		$media_struct = geodiv_lire_media($args_media);
		return $media_struct;	
	}
}

/**
 * Met à jour un média
 */
function geodiv_update_media($args){
	global $spip_xmlrpc_serveur;
	
	/**
	 * On est obligé d'être identifié
	 */
	if(!is_array($GLOBALS['visiteur_session'])){
		$erreur = _T('xmlrpc:erreur_mauvaise_identification');
		return new IXR_Error(-32601, attribut_html($erreur));
	}
	
	$id_article = $args['id_article'];
	
	$args_update_article = array(
		'objet' => 'article',
		'id_objet'=> $id_article,
		'set' => $args
	);
	
	$media = $spip_xmlrpc_serveur->update($args_update_article);
	if(!$media){
		return $spip_xmlrpc_serveur->error;
	}
	else{
		$args_media['id_article'] = $media['result']['id'];
		$media_struct = geodiv_lire_media($args_media);
		return $media_struct;	
	}
}
?>