<?php
/**
 * « Qui sommes-nous ? » : les personnes qui organisent et accompagnent la
 * Convention, affichées sur qui-sommes-nous.php en trois onglets.
 *
 * Tout tient dans lib/equipe.json (repris du tableur de l'équipe) :
 *   chercheurs : [{titre, presentation, membres: [{prenom, nom, photo, affiliation}]}]
 *   codac      : [{prenom, nom, photo, statut, motivation}]
 *   elus       : [{prenom, nom, photo, groupe, mandat}]
 *   groupes_absents : [{groupe, sigle}], groupes de l'Assemblée nationale
 *                     invités qui n'ont désigné personne (affichés après les élu.es)
 * photo est un chemin relatif à la racine du site (images/equipe/…), ou vide :
 * les initiales sont alors affichées à la place. Une motivation vide (non
 * validée par la personne) n'est pas affichée.
 */
declare(strict_types=1);

const EQUIPE_FILE = __DIR__ . '/equipe.json';

function equipe_load(): array
{
    $data = is_file(EQUIPE_FILE) ? json_decode((string) file_get_contents(EQUIPE_FILE), true) : null;
    $data = is_array($data) ? $data : [];

    $projets = [];
    foreach ($data['chercheurs'] ?? [] as $projet) {
        $projets[] = [
            'titre' => (string) ($projet['titre'] ?? ''),
            'presentation' => (string) ($projet['presentation'] ?? ''),
            'membres' => array_map(
                fn(array $p) => equipe_personne($p, ['affiliation']),
                array_filter($projet['membres'] ?? [], 'is_array')
            ),
        ];
    }

    return [
        'chercheurs' => $projets,
        'codac' => array_map(fn(array $p) => equipe_personne($p, ['statut', 'motivation']), array_filter($data['codac'] ?? [], 'is_array')),
        'elus' => array_map(fn(array $p) => equipe_personne($p, ['mandat', 'groupe']), array_filter($data['elus'] ?? [], 'is_array')),
        'groupes_absents' => array_map(
            fn(array $g) => ['groupe' => (string) ($g['groupe'] ?? ''), 'sigle' => (string) ($g['sigle'] ?? '')],
            array_filter($data['groupes_absents'] ?? [], 'is_array')
        ),
    ];
}

/** Une personne, avec les champs communs et ceux propres à son groupe. */
function equipe_personne(array $row, array $fields): array
{
    $p = [
        'prenom' => (string) ($row['prenom'] ?? ''),
        'nom' => (string) ($row['nom'] ?? ''),
        'photo' => (string) ($row['photo'] ?? ''),
    ];
    foreach ($fields as $field) {
        $p[$field] = (string) ($row[$field] ?? '');
    }
    return $p;
}

/** Initiales affichées à la place d'une photo manquante : « Emilie Frenkiel » → « EF ». */
function equipe_initiales(array $p): string
{
    return mb_strtoupper(mb_substr($p['prenom'], 0, 1) . mb_substr($p['nom'], 0, 1));
}
