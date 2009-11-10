<?php

$table_des_traitements['TITRE'][]= 'supprimer_numero(typo(%s))';

// urls prorpes en minuscules

define ('_url_minuscules',1);

/*
// passer les extras du plugin à travers les fonctions de typo de SPIP

$GLOBALS['table_des_traitements']['CLUB_ADRESSE'][]= 'propre(%s)';
$GLOBALS['table_des_traitements']['CLUB_SIEGE_SOCIAL'][]= 'propre(%s)';
$GLOBALS['table_des_traitements']['CLUB_ADRESSE_COURRIER'][]= 'propre(%s)';
$GLOBALS['table_des_traitements']['CLUB_MAIL'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_SITE'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_RESPONSABLE_COM'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_TEL'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_PRESIDENT'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_PRESIDENT_ADRESSE'][]= 'propre(%s)';
$GLOBALS['table_des_traitements']['CLUB_PRESIDENT_TEL'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_SECRETAIRE'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_TRESORIER'][]= 'typo(%s)';
$GLOBALS['table_des_traitements']['CLUB_ASSISTANTS'][]= 'propre(%s)';

// afficher les extras seulement dans la rubrique des clubs
// on fait la verification dans la premiere autorisation et on reprends celle-ci ensuite

function autoriser_article_club_adresse_modifierextra_dist($faire, $type, $id, $qui, $opt){
    $id_secteur = $opt['contexte']['id_secteur'];
    if (!$id_secteur) {
        $id_secteur = sql_getfetsel("id_secteur", "spip_articles", "id_article=".intval($id));
    }
    if ($id_secteur == lire_config('fjka/rubrique_clubs', 16)) {
        return true;
    }
    return false;
}
function autoriser_article_club_adresse_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_siege_social_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_siege_social_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_adresse_courrier_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_adresse_courrier_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_mail_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_mail_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_site_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_site_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_responsable_com_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_responsable_com_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_tel_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_tel_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_president_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_president_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_president_adresse_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_president_adresse_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_president_tel_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_president_tel_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_secretaire_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_secretaire_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_tresorier_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_tresorier_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}

function autoriser_article_club_assistants_modifierextra_dist($faire, $type, $id, $qui, $opt){
	 return autoriser('article_club_adresse_modifierextra', $type, $id, $qui, $opt);
}
function autoriser_article_club_assistants_voirextra_dist($faire, $type, $id, $qui, $opt) {
    return autoriser('modifierextra', $type, $id, $qui, $opt);
}
*/
?>