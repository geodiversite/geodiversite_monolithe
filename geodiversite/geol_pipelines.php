<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Insertion dans le pipeline insert_head_css (SPIP)
 * 
 * Ajout de la feuille de styles calculée de geodiversite
 * 
 * @param array $flux
 * @return array $flux
 */
function geol_insert_head_css($flux) {
	$flux .= '<link rel="stylesheet" href="'.parametre_url(generer_url_public('geol.css'),'ltr', $GLOBALS['spip_lang_left']).'" type="text/css" media="projection, screen, tv" />';
	return $flux;
}

/**
 * Insertion dans le pipeline styliser (SPIP)
 * 
 * Par défaut, appliquer la composition 'page' aux articles de la rubrique -1 (les pages donc)
 * 
 * @param array $flux
 * @return array $flux
 */
function geol_styliser($flux){
	$squelette = $flux['data'];
	$fond = $flux['args']['fond'];
	$ext = $flux['args']['ext'];
	if ($flux['args']['id_rubrique'] == '-1'
		&& $flux['args']['contexte']['type-page'] == 'article'
		&& $flux['args']['contexte']['composition'] == ''
	){
		$flux['args']['contexte']['composition'] = "page";
		if (isset($flux['args']['contexte']['composition'])
			AND $f=find_in_path($fond."-".$flux['args']['contexte']['composition'].".$ext")){
				$flux['data'] = substr($f,0,-strlen(".$ext"));
		}
	}
	return $flux;
}

/**
 * Insertion dans le pipeline formulaire_charger (SPIP)
 * 
 * Surcharge du sujet et le texte du message généré par le formulaire_ecrire_auteur
 * Surcharge du formulaire d'inscription pour ne pas afficher l'explication
 * 
 * @param array $flux
 * @return array $flux
 */
function geol_formulaire_charger($flux){
	// sujet perso pour formulaire_ecrire_auteur depuis une page article (erreur de localisation)
	if (($flux['args']['form']=='ecrire_auteur') AND ($flux['args']['args'][1]!='')) {
		$flux['data']['sujet_message_auteur'] .= supprimer_tags(extraire_multi($GLOBALS['meta']['nom_site']))." : "._T('geol:sujet_erreur_localisation');
		$flux['data']['texte_message_auteur'] .= _T('geol:depuis_page')." : ".generer_url_entite_absolue($flux['args']['args'][1],'article')."\n\nMessage :\n\n";
	}
	// pas d'explicaltion sur le form d'inscription
	if (($flux['args']['form']=='inscription') AND ($flux['args']['args'][0]=='1comite')) {
		$flux['data']['_commentaire'] = '';
	}
	return $flux;
}

/**
 * Insertion dans le pipeline pre_boucle (SPIP)
 * 
 * Forcer le critère {tout} sur les boucles rubriques
 * 
 * @param array $boucle
 * @return array $boucle
 */
function geol_pre_boucle($boucle){
	if ($boucle->type_requete == 'rubriques' AND !isset($boucle->modificateur['criteres']['statut']))
		$boucle->modificateur['criteres']['statut'] = true;
	return $boucle;
}

/**
 * Insertion dans le pipeline notifications_destinataires (plugin notifications)
 *
 * Ajouter les auteurs qui ont déjà posté sous l'article aux destinataires (mode thread pour forums à plat)
 *
 * @param array $flux
 * @return array
 */
function geol_notifications_destinataires($flux){
	$quoi = $flux['args']['quoi'];
	$options = $flux['args']['options'];
	// forum valide sur un article : prevenir les auteurs qui ont déjà posté sous l'article seulement si leur message est publié
	if ($quoi=='forumvalide' AND $GLOBALS['notifications']['thread_forum']) {
		$id_forum = $flux['args']['id'];
		if (($t = $options['forum'] OR $t = sql_fetsel("*", "spip_forum", "id_forum=" . intval($id_forum)))
			AND $t['objet'] == 'article'
		) {
			// Tous les participants a ce *thread*, abonnes
			// on prend les emails parmi notification_email (prioritaire si rempli) email_auteur ou email de l'auteur qd id_auteur connu
			$s = sql_select("F.email_auteur, F.notification_email, A.email",
				"spip_forum AS F LEFT JOIN spip_auteurs AS A ON F.id_auteur=A.id_auteur",
				"notification=1 AND F.statut='publie' AND F.objet='article' AND F.id_objet=" . intval($t['id_objet']) . " AND (email_auteur != '' OR notification_email != '' OR A.email IS NOT NULL )");
			while ($r = sql_fetch($s)){
				if ($r['notification_email'])
					$flux['data'][] = $r['notification_email'];
				elseif($r['email_auteur'])
					$flux['data'][] = $r['email_auteur'];
				elseif($r['email'])
					$flux['data'][] = $r['email'];
			}
		}
	}
	return $flux;
}

/**
 * Insertion dans le pipeline em_post_upload_medias (plugin Emballe médias)
 * 
 * Dans le cas des fichiers jpg, si on a récup une date, on l'assigne à l'article
 * 
 * @param array $flux
 * @return array $flux
 */
function geol_em_post_upload_medias($flux){

	spip_log("EM EXIFS : mime-type = ".$flux['args']['mime'],"emballe_medias");
	
	if ($flux['args']['mime'] == 'image/jpeg') {
		$id_document = $flux['args']['id_document'];
		$id_article = $flux['args']['id_objet'];
		$fichier = sql_getfetsel("fichier","spip_documents","id_document=".intval($id_document));
		include_spip('inc/documents');
		$fichier = get_spip_doc($fichier);
		// on recupere la date definie dans les donnees EXIF du document s'il y en a
		if (($exifs =  @exif_read_data($fichier,'EXIF')) && ($date_exifs = $exifs['DateTimeOriginal'])) {
			spip_log("EM EXIFS : recuperation de la date du fichier $fichier","emballe_medias");
			$date = date("Y-m-d H:i:s",strtotime($date_exifs));
			sql_updateq('spip_articles', array('date'=> $date), "id_article=$id_article");
			spip_log("EM EXIFS : Update de la date depuis EXIFS pour l'article $id_article => date = $date","emballe_medias");
		}
	}
	
	return $flux;
}


/**
 * Insertion dans le pipeline xmlrpc_methodes (xmlrpc)
 * Ajout de méthodes xml-rpc spécifiques à Geodiversite
 * 
 * @param array $flux : un array des methodes déjà présentes, fonctionnant sous la forme :
 * -* clé = nom de la méthode;
 * -* valeur = le nom de la fonction à appeler;
 * @return array $flux : l'array complété avec nos nouvelles méthodes 
 */
function geol_xmlrpc_methodes($flux){
	$flux['geodiv.liste_medias'] = 'geodiv_liste_medias';
	$flux['geodiv.lire_media'] = 'geodiv_lire_media';
	$flux['geodiv.creer_media'] = 'geodiv_creer_media';
	$flux['geodiv.update_media'] = 'geodiv_update_media';
	return $flux;
}

/**
 * Insertion dans le pipeline xmlrpc_server_class (xmlrpc)
 * Ajout de fonctions spécifiques utilisées par le serveur xml-rpc 
 */
function geol_xmlrpc_server_class($flux){
	include_spip('inc/geol_xmlrpc');
	return $flux;
}

?>