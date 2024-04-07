<?php

namespace App\Controller;

use App\Model\Annonce;
use App\Model\Annonceur;

class AddItemController
{
    /**
     * Affiche le formulaire d'ajout d'un nouvel item
     *
     * @param \Twig\Environment $twig Environnement Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param array $cat Liste des catégories
     * @param array $dpt Liste des départements
     * @return void
     */
    function addItemView(\Twig\Environment $twig, array $menu, string $chemin, array $cat, array $dpt): void
    {
        $template = $twig->load("add.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "categories" => $cat,
            "departements" => $dpt
        ]);
    }

    /**
     * Vérifie si une chaîne est un email valide
     *
     * @param string $email Adresse email à vérifier
     * @return bool True si l'email est valide, sinon False
     */
    private function isEmail(string $email): bool
    {
        return (bool)preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email);
    }

    /**
     * Ajoute un nouvel item
     *
     * @param \Twig\Environment $twig Environnement Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param array $allPostVars Données POST du formulaire
     * @return void
     */
    function addNewItem(\Twig\Environment $twig, array $menu, string $chemin, array $allPostVars): void
    {
        date_default_timezone_set('Europe/Paris');

        $errors = $this->validateFormData($allPostVars);

        if (!empty($errors)) {
            $this->displayErrorPage($twig, $menu, $chemin, $errors);
        } else {
            try {
                $annonceur = $this->createAnnonceur($allPostVars);
                $annonce = $this->createAnnonce($allPostVars);
                $this->associateAnnonceToAnnonceur($annonceur, $annonce);

                $this->displayConfirmationPage($twig, $menu, $chemin);
            } catch (\Exception $e) {
                $this->handleDatabaseError($e);
            }
        }
    }

    /**
     * Valide les données du formulaire
     *
     * @param array $formData Données du formulaire
     * @return array Tableau des erreurs de validation
     */
    function validateFormData(array $formData): array
    {
        $errors = [];

        $nom = filter_var(trim($formData['nom']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($formData['email']), FILTER_VALIDATE_EMAIL);
        // Assurez-vous de valider et nettoyer correctement d'autres champs également

        if (empty($nom)) {
            $errors['nameAdvertiser'] = 'Veuillez entrer votre nom';
        }
        if ($email === false) {
            $errors['emailAdvertiser'] = 'Veuillez entrer une adresse mail correcte';
        }

        // Validez et nettoyez les autres champs et ajoutez les erreurs au tableau $errors

        return $errors;
    }

    /**
     * Crée un nouvel annonceur à partir des données du formulaire
     *
     * @param array $data Données du formulaire
     * @return Annonceur Nouvel annonceur créé
     */
    function createAnnonceur(array $data): Annonceur
    {
        return Annonceur::create([
            'email' => $data['email'],
            'nom_annonceur' => $data['nom'],
            'telephone' => $data['phone']
        ]);
    }

    /**
     * Crée une nouvelle annonce à partir des données du formulaire
     *
     * @param array $data Données du formulaire
     * @return Annonce Nouvelle annonce créée
     */
    function createAnnonce(array $data): Annonce
    {
        return Annonce::create([
            'ville' => $data['ville'],
            'id_departement' => $data['departement'],
            'prix' => $data['price'],
            'mdp' => password_hash($data['psw'], PASSWORD_DEFAULT),
            'titre' => $data['title'],
            'description' => $data['description'],
            'id_categorie' => $data['categorie'],
            'date' => date('Y-m-d')
        ]);
    }

    /**
     * Associe une annonce à un annonceur
     *
     * @param Annonceur $annonceur Annonceur
     * @param Annonce $annonce Annonce
     * @return void
     */
    function associateAnnonceToAnnonceur(Annonceur $annonceur, Annonce $annonce): void
    {
        $annonceur->annonce()->save($annonce);
    }

    /**
     * Affiche la page d'erreur en cas de validation échouée
     *
     * @param \Twig\Environment $twig Environnement Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @param array $errors Erreurs de validation
     * @return void
     */
    function displayErrorPage(\Twig\Environment $twig, array $menu, string $chemin, array $errors): void
    {
        $template = $twig->load("add-error.html.twig");
        echo $template->render([
            "breadcrumb" => $menu,
            "chemin" => $chemin,
            "errors" => $errors
        ]);
        exit();
    }

    /**
     * Affiche la page de confirmation après ajout réussi
     *
     * @param \Twig\Environment $twig Environnement Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base
     * @return void
     */
    function displayConfirmationPage(\Twig\Environment $twig, array $menu, string $chemin): void
    {
        $template = $twig->load("add-confirm.html.twig");
        echo $template->render(["breadcrumb" => $menu, "chemin" => $chemin]);
    }

    /**
     * Gère les erreurs de base de données
     *
     * @param \Exception $exception Exception générée
     * @return void
     */
    function handleDatabaseError(\Exception $exception): void
    {
        // Enregistre l'erreur pour le débogage
        error_log($exception->getMessage());
        // Redirige vers une page d'erreur générique
        header("Location: /error-page");
        exit();
    }
}
