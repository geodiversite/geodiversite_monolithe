<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

// titres sans numero
$table_des_traitements['TITRE'][]= 'supprimer_numero(typo(%s))';

// urls prorpes en minuscules
define ('_url_minuscules',1);

// autoriser le prive uniquement pour les admins
function autoriser_ecrire($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo' AND !$qui['restreint'];
}

// surcharger autoriser_ecrire_ticket_dist sinon autoriser_ecrire passe avant
function autoriser_ticket_ecrire($faire, $type, $id, $qui, $opt) {
	include_spip('inc/tickets_autoriser');
	return autoriser_ticket_ecrire_dist($faire, $type, $id, $qui, $opt);
}

// surcharger autoriser_modererforum_article pour ne pas envoyer de notifs avec un lien vers le privé aux rédacteurs
function autoriser_modererforum($faire, $type, $id, $qui, $opt) {
	return autoriser_ecrire($faire, $type, $id, $qui, $opt);
}

// construction du mail envoyant les identifiants 
// fonction redefinissable qui doit retourner un tableau
// dont les elements seront les arguments de inc_envoyer_mail

function envoyer_inscription($desc, $nom, $mode, $id) {

	$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']["nom_site"]);
	$adresse_site = $GLOBALS['meta']["adresse_site"];
	$adresse_login = $adresse_site;
	if ($mode == '6forum') {
		$msg = 'form_forum_voici1';
	} else {
		$msg = 'form_forum_voici2';
	}

	$msg = _T('form_forum_message_auto')."\n\n"
		. _T('form_forum_bonjour', array('nom'=>$nom))."\n\n"
		. _T($msg, array('nom_site_spip' => $nom_site_spip,
			'adresse_site' => $adresse_site . '/',
			'adresse_login' => $adresse_login . '/')) . "\n\n- "
		. _T('form_forum_login')." " . $desc['login'] . "\n- "
		. _T('form_forum_pass'). " " . $desc['pass'] . "\n\n";

	return array("[$nom_site_spip] "._T('form_forum_identifiants'), $msg);
}

?>