<?php

namespace App\controller;

use App\model\Annonce;
use model\Photo;
use model\Annonceur;

class IndexController
{
    protected $annonce = array();

    /**
     * Affiche toutes les annonces avec Twig
     *
     * @param \Twig\Environment $twig Instance de Twig
     * @param array $menu Menu de navigation
     * @param string $chemin Chemin de base de l'application
     * @param array $cat Catégories disponibles
     * @return void
     */
    public function displayAllAnnonce($twig, $menu, $chemin, $cat)
    {
        $template = $twig->load("index.html.twig");
        $menu     = array(
            array(
                'href' => $chemin,
                'text' => 'Accueil'
            ),
        );

        $this->getAllAnnonces($chemin);
        echo $template->render(array(
            "breadcrumb" => $menu,
            "chemin"     => $chemin,
            "categories" => $cat,
            "annonces"   => $this->annonce
        ));
    }

    /**
     * Récupère toutes les annonces depuis la base de données
     * et les prépare pour l'affichage
     *
     * @param string $chemin Chemin de base de l'application
     * @return void
     */
    protected function getAllAnnonces($chemin)
    {
        $tmp     = Annonce::with("Annonceur")->orderBy('id_annonce', 'desc')->take(12)->get();
        $annonce = [];
        foreach ($tmp as $t) {
            $t->nb_photo = Photo::where("id_annonce", "=", $t->id_annonce)->count();
            if ($t->nb_photo > 0) {
                $t->url_photo = Photo::select("url_photo")
                    ->where("id_annonce", "=", $t->id_annonce)
                    ->first()->url_photo;
            } else {
                $t->url_photo = '/img/noimg.png';
            }
            $t->nom_annonceur = Annonceur::select("nom_annonceur")
                ->where("id_annonceur", "=", $t->id_annonceur)
                ->first()->nom_annonceur;
            array_push($annonce, $t);
        }
        $this->annonce = $annonce;
    }
}
