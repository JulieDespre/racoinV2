<?php
namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;
use App\Model\Categorie;
use App\Model\Departement;
use App\Model\Photo;

/**
 * Contrôleur gérant les actions relatives aux items.
 */
class ItemController {

    /**
     * Constructeur de la classe ItemController.
     */
    public function __construct() {
    }

    /**
     * Affiche les détails d'un item.
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param int $n Identifiant de l'item
     * @param array $cat Liste des catégories
     * @return void
     */
    public function afficherItem(\Twig\Environment $twig, array $menu, string $chemin, int $n, array $cat): void {
        $this->annonce = Annonce::find($n);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }

        $menu = $this->buildMenu($chemin, $n, $cat);

        $this->loadTemplate($twig, "itemController.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->loadAnnonceur($this->annonce->id_annonceur),
            "dep" => $this->loadDepartementName($this->annonce->id_departement),
            "photo" => $this->loadPhotos($n),
            "categories" => $cat
        ]);
    }

    /**
     * Affiche le formulaire de suppression d'un item (méthode GET).
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param int $n Identifiant de l'item
     * @return void
     */
    public function supprimerItemGet(\Twig\Environment $twig, array $menu, string $chemin, int $n): void {
        $this->annonce = Annonce::find($n);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }
        $this->loadTemplate($twig, "delGet.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ]);
    }

    /**
     * Supprime un item (méthode POST).
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param int $n Identifiant de l'item
     * @param array $cat Liste des catégories
     * @return void
     */
    public function supprimerItemPost(\Twig\Environment $twig, array $menu, string $chemin, int $n, array $cat): void {
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
            $this->deletePhotos($n);
            $this->annonce->delete();
        }

        $this->loadTemplate($twig, "delPost.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "pass" => $reponse,
            "categories" => $cat
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un item (méthode GET).
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param int $id Identifiant de l'item
     * @return void
     */
    public function modifyGet(\Twig\Environment $twig, array $menu, string $chemin, int $id): void {
        $this->annonce = Annonce::find($id);
        if (!isset($this->annonce)) {
            echo "404";
            return;
        }
        $this->loadTemplate($twig, "modifyGet.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce
        ]);
    }

    /**
     * Traite la modification d'un item (méthode POST).
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param int $n Identifiant de l'item
     * @param array $cat Liste des catégories
     * @param array $dpt Liste des départements
     * @return void
     */
    public function modifyPost(\Twig\Environment $twig, array $menu, string $chemin, int $n, array $cat, array $dpt): void {
        $this->annonce = Annonce::find($n);
        $reponse = false;
        if (password_verify($_POST["pass"], $this->annonce->mdp)) {
            $reponse = true;
            $this->updateAnnonce($n, $cat, $dpt);
        }

        $this->loadTemplate($twig, "modifyPost.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "annonce" => $this->annonce,
            "annonceur" => $this->loadAnnonceur($this->annonce->id_annonceur),
            "pass" => $reponse,
            "categories" => $cat,
            "departements" => $dpt,
            "dptItem" => $this->loadDepartementName($this->annonce->id_departement),
            "categItem" => $this->loadCategorieName($this->annonce->id_categorie)
        ]);
    }

    // Fonctions utilitaires

    /**
     * Construit le menu de navigation.
     *
     * @param string $chemin Chemin de base
     * @param int $n Identifiant de l'item
     * @param array $cat Liste des catégories
     * @return array
     */
    private function buildMenu(string $chemin, int $n, array $cat): array {
        return [
            ["href" => $chemin, "text" => "Acceuil"],
            ["href" => $chemin."/cat/".$n, "text" => Categorie::find($n)?->nom_categorie],
            ["href" => $chemin."/itemController/".$n, "text" => $this->annonce->titre]
        ];
    }

    /**
     * Charge les détails de l'annonceur.
     *
     * @param int $id Identifiant de l'annonceur
     * @return mixed
     */
    private function loadAnnonceur(int $id) {
        return Annonceur::find($id);
    }

    /**
     * Charge le nom du département.
     *
     * @param int $id Identifiant du département
     * @return string|null
     */
    private function loadDepartementName(int $id): ?string {
        $departement = Departement::find($id);
        return $departement ? $departement->nom_departement : null;
    }

    /**
     * Charge les photos de l'annonce.
     *
     * @param int $n Identifiant de l'annonce
     * @return mixed
     */
    private function loadPhotos(int $n) {
        return Photo::where('id_annonce', '=', $n)->get();
    }

    /**
     * Supprime les photos de l'annonce.
     *
     * @param int $n Identifiant de l'annonce
     * @return void
     */
    private function deletePhotos(int $n): void {
        Photo::where('id_annonce', '=', $n)->delete();
    }

    /**
     * Met à jour les détails de l'annonce.
     *
     * @param int $n Identifiant de l'annonce
     * @param array $data Données à mettre à jour
     * @param int $id Identifiant de l'annonce
     * @return void
     */
    private function updateAnnonce(int $n, array $data, int $id): void {
        // Implementer la logique de mise à jour de l'annonce
    }

    /**
     * Valide les données du formulaire.
     *
     * @param array $formData Données du formulaire
     * @return array Tableau des erreurs
     */
    private function validateFormData(array $formData): array {
        $errors = [];
        // Ajoutez les validations nécessaires ici
        return $errors;
    }

    /**
     * Affiche une page d'erreur.
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param array $errors Tableau des erreurs
     * @return void
     */
    private function displayErrorPage(\Twig\Environment $twig, array $menu, string $chemin, array $errors): void {
        $this->loadTemplate($twig, "add-error.html.twig", [
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "errors" => $errors
        ]);
    }

    /**
     * Charge et affiche un template Twig.
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param string $templateName Nom du template
     * @param array $data Données à passer au template
     * @return void
     */
    private function loadTemplate(\Twig\Environment $twig, string $templateName, array $data): void {
        $template = $twig->load($templateName);
        echo $template->render($data);
    }
}
