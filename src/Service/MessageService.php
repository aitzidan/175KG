<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MessageService
{
    public function checkMessage($CODEERROR)
    {
        $listMessage = array(
            "ERREUR-PARAMS-VIDE" => "Un ou plusieurs champs obligatoires sont vides !",
            "ERREUR-PARAMS-CONNEXION" => "L'email ou le mot de passe est incorrect !",
            "ERREUR-EXCEPTION" => "Une erreur s'est produite !",
            "ERREUR" => "Une erreur s'est produite !",
            "OK" => "Opération terminée avec succès",
            "FORMAT-MOT-DE-PASSE" => "Le mot de passe doit comporter au moins 8 caractères et inclure au moins un chiffre, une lettre majuscule, une lettre minuscule et un caractère spécial !",
            "MOT-DE-PASSE-CORRESPONDANCE" => "Les mots de passe ne correspondent pas !",
            "EMAIL-INVALIDE" => "Format d'email invalide. Veuillez saisir une adresse email valide !",
            "TELEPHONE-INVALIDE" => "Format de numéro de téléphone invalide. Veuillez saisir un numéro de téléphone numérique à 10 chiffres !",
            "NOM-INVALIDE" => "Format de nom invalide. Veuillez saisir un nom valide d'au moins 2 caractères !",
            "PROFIL-NOT-EXIST" => "Le profil spécifié n'existe pas !",
            "EMAIL_EXIST" => "Email déjà existant !",
            "ERROR" => "Une erreur s'est produite !",
            "ERROR_ENTETE" => "Veuillez vérifier l'entête du fichier",
            "ERROR_DATE" => "Veuillez vérifier votre date",
            "ERROR_FILE" => "Veuillez vérifier l'entête du fichier",
            "ERROR_UPLOAD_DATA" => "Erreur dans la sauvegarde des données",
            "ERREUR-TELEPHONE-INVALID"=>"Veuillez vérifier votre format télephone"
        );

        return $listMessage[$CODEERROR];
    }



}