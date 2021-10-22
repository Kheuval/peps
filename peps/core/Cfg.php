<?php

declare(strict_types=1);

namespace peps\core;

use Locale;
use NumberFormatter;

/**
 * Classe 100% statique de configuration initiale de l'application.
 * DOIT être étendue dans l'application par une classe de configuration générale elle-même étendue par une classe finale par serveur.
 * Extension PHP 'intl' requise.
 */
class Cfg
{
    /**
     * Le tableau associatif des 'constantes' de configuration.
     * 
     * @var mixed[]
     */
    private static array $constants = [];

    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Inscrit les 'constantes' de base.
     * DOIT être redéfinie dans la classe enfant pour y inscrire les 'constantes' de l'application en invoquant parent::init() en première instruction.
     * Cette méthode DOIT rester protected sauf au dernier niveau d'héritage, auquel elle DOIT passer public pour être invoquée depuis le contrôleur frontal.
     * Les clefs (en SNAKE_CASE) enregistrées ici sont les SEULES accessibles aux classes PEPS.
     * Les clefs ajoutées par l'application DEVRAIENT être en camelCase.
     */
    protected static function init(): void
    {
        // Chemin du fichier JSON des routes depuis la racine de l'application.
        self::register('ROUTE_FILE', 'cfg' . DIRECTORY_SEPARATOR . 'routes.json');
        // Namespace des contrôleurs.
        self::register('CONTROLLERS_NAMESPACE', 'controllers');
        // Chemin du répertoire des vues depuis la racine de l'application.
        self::register('VIEWS_DIR', 'views');
        // Nom de la vue affichant l'erreur 404.
        self::register('ERROR_404_VIEW', 'error404.php');
        // Locale par défaut en cas de non-détection (ex : 'fr' ou 'fr-FR').
        self::register('LOCALE_DEFAULT', 'fr');
        // Locale du client.
        self::register('LOCALE', (function () {
            // Récupérer les locales du navigateur.
            $locales = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING);
            return Locale::acceptFromHttp($locales) ?: self::$constants['LOCALE_DEFAULT'];
        })());
        // Instance de NumberFormatter pour formater un nombre avec 2 décimales selon la locale.
        self::register('NF_LOCALE_2DEC', (fn () => NumberFormatter::create(self::$constants['LOCALE'], NumberFormatter::PATTERN_DECIMAL, '#,##0.00'))());
        // Instance de NumberFormatter pour formater un nombre avec 2 décimales selon la norme US sans séparateur de milliers, typiquement pour les champs input de type number de certains navigateurs.
        self::register('NF_INPUT_2DEC', (fn () => NumberFormatter::create('en-US', NumberFormatter::PATTERN_DECIMAL, '0.00'))());
        // Constante du mode PERSISTENT.
        self::register('SESSION_PERSISTENT', 'SESSION_PERSISTENT');
        // Constante du mode HYBRID.
        self::register('SESSION_HYBRID', 'SESSION_HYBRID');
        // Constante du mode ABSOLUTE.
        self::register('SESSION_ABSOLUTE', 'SESSION_ABSOLUTE');
        // Constante de l'option STRICT de "cookie_samesite" des sessions.
        self::register('COOKIE_SAMESITE_STRICT', 'STRICT');
        // Constante de l'option LAX de "cookie_samesite" des sessions.
        self::register('COOKIE_SAMESITE_LAX', 'LAX');
        // Constante de l'option NONE de "cookie_samesite" des sessions.
        self::register('COOKIE_SAMESITE_NONE', 'NONE');
    }

    /**
     * Inscrit une 'constante' dans le tableau des 'constantes'.
     */
    protected final static function register(string $key, mixed $val = null): void
    {
        self::$constants[$key] = $val;
    }

    /**
     * Retourne la valeur de la 'constante' à partir de sa clef.
     * Retourne null si clef absente.
     */
    public final static function get(string $key): mixed
    {
        return self::$constants[$key] ?? null;
    }
}
