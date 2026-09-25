<?php
require_once __DIR__ . '/lib/layout.php';

$actus = actus_load_all();
// Lien partagé (?actu=<slug>) : la pop-in s'ouvre au chargement (assets/actus.js)
// et les balises Open Graph décrivent l'actualité plutôt que le site.
$actu_partagee = isset($_GET['actu']) ? actu_load((string) $_GET['actu']) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<script defer data-domain="conventioncitoyennepourlademocratie.fr" src="https://plausible.services.dataforgood.fr/js/script.file-downloads.hash.outbound-links.pageview-props.tagged-events.js"></script>
<script>window.plausible = window.plausible || function() { (window.plausible.q = window.plausible.q || []).push(arguments) }</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Convention Citoyenne pour la Démocratie. Près de 80 citoyens, parlementaires et membres de la société civile délibèrent à Lille pour répondre à la crise démocratique avant l’élection présidentielle de 2027.">
<title>Convention Citoyenne pour la Démocratie</title>
<?php layout_og_tags(SITE_NAME, SITE_DESCRIPTION, site_url() . '/', $actu_partagee); ?>
<link rel="icon" type="image/png" href="./images/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Noto+Serif:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://sibforms.com/forms/end-form/build/sib-styles.css">
<link rel="stylesheet" href="./assets/style.css">
</head>

<body>

<!-- ============================ NAV ============================ -->
<?php layout_nav(true); ?>

<!-- ============================ HERO ============================ -->
<header class="hero">
  <div class="wrap hero-grid">
    <div class="hero-content">
      <span class="eyebrow">Septembre à décembre 2026</span>
      <h1 class="title hero-title">Écrire ensemble la démocratie de demain.</h1>
      <p class="lead hero-lead">Citoyen.nes tiré.es au sort, parlementaires, société civile : près de 80 personnes délibèrent avant la présidentielle 2027 pour répondre à la crise démocratique.</p>
      <div class="hero-actions">
        <a href="#newsletter" class="btn btn-primary">Suivre la convention</a>
        <a href="#projet" class="btn-link">Comprendre le projet ↓</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="./images/1.jpg" alt="Portrait d’une participante de la Convention Citoyenne">
    </div>
  </div>
</header>

<?php layout_actus_home($actus); ?>

<!-- ============================ INTRO NARRATIVE ============================ -->
<section class="section" id="projet">
  <div class="wrap">
    <div class="intro-grid reveal">
      <div class="intro-media">
        <img src="./images/5.jpg" alt="Délibération citoyenne">
      </div>
      <div class="intro-narrative">
        <span class="eyebrow bleu">Pourquoi cette convention</span>
        <h2 class="title section-title">La démocratie française traverse une crise de confiance que les institutions seules ne peuvent plus résoudre.</h2>
        <p>Fragmentation politique, polarisation du débat public, défiance citoyenne : les compromis deviennent difficiles, les institutions se fragilisent. Face à ce constat, des citoyen.nes, des chercheur.euses et des associations agissent ensemble pour imaginer la démocratie de demain.</p>
        <p>La Convention Citoyenne pour la Démocratie s’ouvre en septembre 2026 pour travailler à des préconisations concrètes, adressées aux candidat.es à l’élection présidentielle de 2027.</p>
        <div class="intro-france2030">
          <img src="./images/france_2030.jpg" alt="France 2030">
          <span>Un projet soutenu dans le cadre<br>du programme national France 2030.</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ 6 POINTS ============================ -->
<section class="section grey">
  <div class="wrap">
    <div class="points-header reveal">
      <div>
        <span class="eyebrow">La convention en 6 points</span>
        <h2 class="title section-title">Un dispositif inédit, expliqué simplement.</h2>
      </div>
      <p>Une convention citoyenne réunit des personnes tirées au sort pour délibérer sur une question de société. Celle-ci va plus loin : pour la première fois en France, elle associe citoyen.nes, parlementaires et société civile.</p>
    </div>

    <div class="points-grid reveal-stagger">
      <div class="point">
        <div class="point-num tech">01 · Pourquoi maintenant</div>
        <h3 class="point-title">Répondre à la crise des institutions</h3>
        <p class="point-desc">Blocages institutionnels, défiance citoyenne, fragmentation politique : l’innovation démocratique comme réponse.</p>
      </div>
      <div class="point">
        <div class="point-num tech">02 · Qui délibère</div>
        <h3 class="point-title">Près de 80 personnes, trois groupes</h3>
        <p class="point-desc">50 citoyen.nes tiré.es au sort, 11 parlementaires transpartisan.es et une vingtaine de membres de la société civile.</p>
      </div>
      <div class="point">
        <div class="point-num tech">03 · Un dispositif inédit</div>
        <h3 class="point-title">Une première en France</h3>
        <p class="point-desc">Pour la première fois, citoyen.nes et élu.es délibèrent ensemble pour imaginer le futur de la démocratie.</p>
      </div>
      <div class="point">
        <div class="point-num tech">04 · Sur quoi</div>
        <h3 class="point-title">Trois grands chantiers</h3>
        <p class="point-desc">La fabrique de la décision publique, l’équilibre des pouvoirs, la représentation politique.</p>
      </div>
      <div class="point">
        <div class="point-num tech">05 · Comment</div>
        <h3 class="point-title">Quatre week-ends à Lille</h3>
        <p class="point-desc">Le samedi et le dimanche, de septembre à décembre 2026. Un travail itératif, session après session, qui se construit pas à pas.</p>
      </div>
      <div class="point">
        <div class="point-num tech">06 · Pour quoi faire</div>
        <h3 class="point-title">Des propositions pour 2027</h3>
        <p class="point-desc">Des préconisations remises aux candidat.es à l’élection présidentielle pour nourrir le débat public.</p>
      </div>
    </div>
  </div>
</section>

<div class="section-divider"></div>

<!-- ============================ PANEL ============================ -->
<section class="section panel-section" id="panel">
  <div class="wrap">
    <div class="panel-grid">
      <div class="panel-text reveal">
        <span class="eyebrow bleu">Le panel</span>
        <h2 class="title section-title">Trois groupes, une même table.</h2>
        <p>Le panel mixte de la Convention rassemble des expériences qui se croisent peu dans le débat public. Cette composition permet de construire des recommandations légitimes et largement appropriables.</p>
        <div style="margin-top: 32px;">
          <a href="#calendrier" class="btn-link">Voir comment ces groupes se rencontrent →</a>
        </div>
      </div>

      <div class="panel-numbers reveal-stagger">
        <div class="panel-card">
          <div class="panel-card-num tech" data-count="50">50</div>
          <div>
            <div class="panel-card-label">Citoyen.nes tiré.es au sort</div>
            <p class="panel-card-desc">Sélectionné.es sur 7 critères socio-démographiques. Contacté.es par téléphone, libres d’accepter.</p>
          </div>
        </div>
        <div class="panel-card">
          <div class="panel-card-num tech" data-count="11">11</div>
          <div>
            <div class="panel-card-label">Parlementaires transpartisan.es</div>
            <p class="panel-card-desc">Une personne désignée par chacun des groupes de l’Assemblée nationale.</p>
          </div>
        </div>
        <div class="panel-card">
          <div class="panel-card-num tech" data-count="20">20</div>
          <div>
            <div class="panel-card-label">Membres de la société civile</div>
            <p class="panel-card-desc">Une vingtaine de personnes, issues notamment du Conseil Économique, Social et Environnemental (CESE).</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ BANDEAU IMAGE ============================ -->
<div class="full-image reveal">
  <img src="./images/2.jpg" alt="">
</div>

<!-- ============================ CALENDRIER ============================ -->
<section class="section" id="calendrier">
  <div class="wrap">
    <div class="reveal" style="max-width: 720px;">
      <span class="eyebrow">Le calendrier</span>
      <h2 class="title section-title">Quatre week-ends à Lille, de septembre à décembre 2026.</h2>
      <p class="lead" style="margin-top: 24px;">La Convention se tient sur quatre week-ends, le samedi et le dimanche, dans la même ville, pour permettre une construction continue des propositions.</p>
    </div>

    <div class="calendar-grid reveal-stagger">
      <div class="session">
        <div class="session-num tech">SESSION 01</div>
        <div class="session-date title">12/13</div>
        <div class="session-month tech">Septembre 2026</div>
      </div>
      <div class="session">
        <div class="session-num tech">SESSION 02</div>
        <div class="session-date title">10/11</div>
        <div class="session-month tech">Octobre 2026</div>
      </div>
      <div class="session">
        <div class="session-num tech">SESSION 03</div>
        <div class="session-date title">14/15</div>
        <div class="session-month tech">Novembre 2026</div>
      </div>
      <div class="session">
        <div class="session-num tech">SESSION 04</div>
        <div class="session-date title">12/13</div>
        <div class="session-month tech">Décembre 2026</div>
      </div>
    </div>

    <figure class="venue-figure reveal">
      <img src="./images/science-po.jpg" alt="L’Atelier de Sciences Po Lille, lieu où se tiendra la Convention">
      <figcaption>L’Atelier · Sciences Po Lille — le lieu de la Convention</figcaption>
    </figure>

    <div class="calendar-milestones reveal-stagger">
      <div class="milestone">
        <div class="milestone-label">Recrutement des citoyen.nes</div>
        <div class="milestone-title">Le tirage au sort a lieu</div>
        <div class="milestone-date tech">Du 15 juin au 15 juillet 2026</div>
      </div>
      <div class="milestone">
        <div class="milestone-label">Les propositions</div>
        <div class="milestone-title">Remises aux candidat.es à la présidentielle</div>
        <div class="milestone-date tech">Janvier – février 2027</div>
      </div>
    </div>

    <div class="calendar-footer reveal">
      <span>Un calendrier pensé pour un travail progressif, session après session.</span>
      <a href="#newsletter" class="btn-link">Recevoir le calendrier détaillé</a>
    </div>
  </div>
</section>

<!-- ============================ LIVRABLE ============================ -->
<section class="section grey">
  <div class="wrap">
    <div class="livrable-grid">
      <div class="livrable-img reveal">
        <img src="./images/3.jpg" alt="">
      </div>
      <div class="livrable-text reveal">
        <span class="eyebrow">Et après ?</span>
        <h2 class="title section-title">Des préconisations qui s’invitent dans le débat présidentiel.</h2>
        <p>À l’issue des quatre sessions, la Convention publiera les préconisations des participant.es pour renforcer la démocratie de demain. Ce document sera remis aux candidat.es à l’élection présidentielle.</p>
        <p>Ces propositions seront diffusées auprès des Françaises et des Français par différents moyens : soirée télévisée, presse, publications scientifiques… avec l’appui de fondations, d’associations, de « think tanks » et d’ambassadeur.rices citoyen.nes.</p>
        <div class="livrable-quote">
          La participation des citoyen.nes, des élu.es et des représentant.es de la société civile permettra à toute la population Française de se reconnaître dans les préconisations de la convention.
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ GOUVERNANCE ============================ -->
<section class="section" id="gouvernance">
  <div class="wrap">
    <div class="gov-header reveal">
      <span class="eyebrow bleu">Gouvernance &amp; écosystème</span>
      <h2 class="title section-title">Un projet de recherche, un programme national, des partenaires.</h2>
      <p class="lead" style="margin-top: 24px;">La Convention Citoyenne pour la Démocratie s’inscrit dans le projet de recherche <a href="https://democis.fr/ " target="_blank" rel="noopener" style="border-bottom:1px solid var(--bleu);">DemoCIS</a>, lauréat d’un programme France 2030 réunissant des centaines de chercheur.euses.</p>
    </div>

    <div class="gov-cols reveal-stagger">
      <div class="gov-col">
        <div class="gov-col-label">Le cadre</div>
        <h3>DemoCIS · France 2030</h3>
        <p>Projet de recherche sur 7 ans réunissant 4 universités, 3 IEP, le CNRS, Inria, l’Institut Mines Télécom. Des centaines de chercheur.euses de plus de 10 disciplines. <a href="https://democis.fr/ " target="_blank" rel="noopener" style="color:var(--rouge);border-bottom:1px solid var(--rouge);">Découvrir DemoCIS →</a></p>
      </div>
      <div class="gov-col">
        <div class="gov-col-label">Le pilotage</div>
        <h3>Comité de pilotage</h3>
        <p>Céline Braconnier, Sandrine Lévêque, Julien Talpin (CNRS, coordinateur), Morgane Fleury, Agnès de Geoffroy.</p>
      </div>
      <div class="gov-col">
        <div class="gov-col-label">L’opération</div>
        <h3>Squada &amp; Missions Publiques</h3>
        <p><a href="https://www.squada.fr" target="_blank" rel="noopener" style="color:var(--rouge);border-bottom:1px solid var(--rouge);">Squada</a> conduit le tirage au sort téléphonique des citoyen.nes. <a href="https://missionspubliques.org" target="_blank" rel="noopener" style="color:var(--rouge);border-bottom:1px solid var(--rouge);">Missions Publiques</a> anime les sessions et conçoit le processus délibératif.</p>
      </div>
    </div>

    <div class="gov-partners reveal">
      <span class="gov-partners-label">Partenaires institutionnels</span>
      <div class="gov-logos">
        <a href="https://democis.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/democis.png" alt="DemoCIS">
        </a>
        <a href="https://www.info.gouv.fr/grand-dossier/france-2030" target="_blank" rel="noopener noreferrer">
          <img src="./images/france_2030.jpg" alt="France 2030">
        </a>
        <a href="https://www.cnrs.fr/fr" target="_blank" rel="noopener noreferrer">
          <img src="./images/cnrs.png" alt="CNRS">
        </a>
        <a href="https://www.inria.fr/fr" target="_blank" rel="noopener noreferrer">
          <img src="./images/inria.png" alt="Inria">
        </a>
        <a href="https://www.imt.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/imt.png" alt="Institut Mines-Télécom">
        </a>
        <a href="https://www.sciencespo-lille.eu/" target="_blank" rel="noopener noreferrer">
          <img src="./images/sciencespologo.png" alt="Sciences Po Lille">
        </a>
        <a href="https://www.sciencespo-grenoble.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/Logo_Sciences Po Grenoble.png" alt="Sciences Po Grenoble">
        </a>
        <a href="https://www.univ-grenoble-alpes.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/Logo_Université_Grenoble_Alpes_2020.png" alt="France 2030">
        </a>
        <a href="https://www.univ-lille.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/Logo_Université_Lille.png" alt="CNRS">
        </a>
        <a href="https://www.sciencespo-saintgermainenlaye.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/SciencesPoStGermain_Logotype 2.png" alt="SciencePoStGermain">
        </a>
        <a href="https://www.univ-lyon3.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/UDL-Lyon3_Vecto.png" alt="Institut Mines-Télécom">
        </a>
        <a href="https://www.cyu.fr/" target="_blank" rel="noopener noreferrer">
          <img src="./images/CergyParisUniversité_CMJN.png" alt="Sciences Po Lille">
        </a>
      </div>

      <!-- <div class="gov-support">
        <span class="gov-partners-label">Avec le soutien de</span>
        <p>La Convention bénéficie de l’appui d’un comité d’accompagnement réunissant fondations, associations et partenaires engagés pour le renouveau démocratique. <em>(Logos et noms des soutiens à intégrer.)</em></p>
      </div> -->
    </div>
  </div>
</section>

<!-- ============================ NEWSLETTER ============================ -->
<section class="newsletter" id="newsletter">
  <div class="newsletter-grid">
    <div class="reveal">
      <h2>Suivez la Convention<br>mois après mois</h2>
      <p>Une lettre mensuelle : les coulisses des sessions, le travail du panel, les préconisations en construction. Rien de plus.</p>
      <div class="newsletter-social">
        <span>Ou suivez-nous :</span>
          <a href="https://www.linkedin.com/company/convention-citoyenne-pour-la-d%C3%A9mocratie/" target="_blank" rel="noopener">LinkedIn</a>
          <a href="https://www.instagram.com/conventioncitoyennedemocratie/" target="_blank" rel="noopener">Instagram</a>
          <a href="https://x.com/ConventionC_" target="_blank" rel="noopener">X / Twitter</a>
          <a href="https://www.facebook.com/ConventionCitoyenneDemocratie" target="_blank" rel="noopener">Facebook</a>
      </div>
    </div>
    <!-- =====================================================
         FORMULAIRE BREVO OFFICIEL — caché visuellement,
         nécessaire pour que main.js fonctionne correctement
         ===================================================== -->
    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
      <div id="sib-form-container" class="sib-form-container">
        <div id="error-message" class="sib-form-message-panel">
          <div class="sib-form-message-panel__text sib-form-message-panel__text--center">
            <span class="sib-form-message-panel__inner-text">Nous n&#39;avons pas pu confirmer votre inscription.</span>
          </div>
        </div>
        <div id="success-message" class="sib-form-message-panel">
          <div class="sib-form-message-panel__text sib-form-message-panel__text--center">
            <span class="sib-form-message-panel__inner-text">Votre inscription est confirmée.</span>
          </div>
        </div>
        <div id="sib-container" class="sib-container--large sib-container--vertical">
          <form
            id="sib-form"
            method="POST"
            action="https://2bed1517.sibforms.com/serve/MUIFACBNylorAoFg3TfTtVGUi27DbVy2HTSKKShcKwy6ebfP3Me6YZt7rI4v7DF8uLwW0nzxCBiU0Lo7g_uR804TW0Mx1lkgBl7HuxlMzQ8ygzi2h0PdeA8bYWlPUjyWbagxNuPV5FjXU01r25Ve-kUK6Kz05mKKWD5ZZLdw7Kyi8HsC5ww7dFqJMGPk_EBRMFcilr5GMDMXnqVCZA=="
            data-type="subscription"
          >
            <div class="sib-input sib-form-block">
              <div class="form__entry entry_block">
                <div class="form__label-row">
                  <div class="entry__field">
                    <input class="input" type="text" id="EMAIL" name="EMAIL" autocomplete="off" value="" placeholder="EMAIL" data-required="true" required />
                  </div>
                </div>
                <label class="entry__error entry__error--primary"></label>
              </div>
            </div>
            <input type="text" name="email_address_check" value="" class="input--hidden">
            <input type="hidden" name="locale" value="fr">
            <!-- Bouton requis par main.js de Brevo (querySelector le cherche) -->
            <button type="submit" id="sib-submit-btn" class="sib-form-block__button sib-form-block__button-with-loader" style="display:none;" tabindex="-1" aria-hidden="true">
              <svg class="icon clickable__icon progress-indicator__icon sib-hide-loader-icon" viewBox="0 0 512 512"><path d="M460.116 373.846l-20.823-12.022c-5.541-3.199-7.54-10.159-4.663-15.874 30.137-59.886 28.343-131.652-5.386-189.946-33.641-58.394-94.896-95.833-161.827-99.676C261.028 55.961 256 50.751 256 44.352V20.309c0-6.904 5.808-12.337 12.703-11.982 83.556 4.306 160.163 50.864 202.11 123.677 42.063 72.696 44.079 162.316 6.031 236.832-3.14 6.148-10.75 8.461-16.728 5.01z"/></svg>
              S'inscrire
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- =====================================================
         FORMULAIRE CUSTOM — votre design, délègue à Brevo
         ===================================================== -->
    <div class="newsletter-form reveal" id="newsletter-custom-form">
      <span class="newsletter-formlabel">Inscrivez votre email ci-dessous</span>

      <div class="newsletter-input">
        <label for="EMAIL-visible" style="position:absolute;left:-9999px;">Adresse email</label>
        <input
          id="EMAIL-visible"
          type="email"
          placeholder="votre.email@exemple.fr"
          autocomplete="email"
          required
        >
        <button type="button" id="newsletter-submit-btn">Je m’inscris</button>
      </div>

      <small>Une lettre par mois. Pas de spam. Désabonnement en un clic.</small>

      <div id="newsletter-success" style="display:none; margin-top:16px; padding:14px 18px; background:rgba(255,255,255,0.15); color:#fff; font-family:var(--font-tech); font-size:13px; letter-spacing:0.04em;">
        ✓ Votre inscription est confirmée.
      </div>
      <div id="newsletter-error" style="display:none; margin-top:16px; padding:14px 18px; background:rgba(0,0,0,0.2); color:#fff; font-family:var(--font-tech); font-size:13px; letter-spacing:0.04em;">
        ✗ Adresse invalide ou déjà inscrite. Veuillez réessayer.
      </div>
    </div>

  </div>
</section>

<!-- ============================ RESSOURCES ============================ -->
<section class="section">
  <div class="wrap">
    <div class="reveal" style="max-width: 720px;">
      <span class="eyebrow bleu">Ressources &amp; presse</span>
      <h2 class="title section-title">Pour aller plus loin.</h2>
    </div>

    <div class="resources-grid reveal-stagger">
      <a class="resource" href="https://www.lemonde.fr/idees/article/2026/07/01/la-sequence-electorale-de-2027-constitue-une-occasion-unique-de-discuter-serieusement-et-collectivement-de-l-avenir-de-la-democratie_6717482_3232.html" target="_blank" rel="noopener">
        <div class="resource-source">Le Monde · Tribune · Réservé aux abonnés</div>
        <div class="resource-title">La séquence électorale de 2027 constitue une occasion unique de discuter sérieusement et collectivement de l’avenir de la démocratie</div>
        <span class="resource-arrow">Lire la tribune →</span>
      </a>
      <a class="resource" href="https://www.ouest-france.fr/elections/presidentielle/elle-se-reunira-a-lille-a-la-rentree-une-convention-citoyenne-au-chevet-de-la-democratie-d8819770-711b-11f1-a0e7-4fa27dc4c816" target="_blank" rel="noopener">
        <div class="resource-source">Ouest France · Article · Réservé aux abonnés</div>
        <div class="resource-title"> Elle se réunira à Lille, à la rentrée : une convention citoyenne au chevet de la démocratie</div>
        <span class="resource-arrow">Lire l'article →</span>
      </a>
      <a class="resource" href="https://www.lemonde.fr/idees/article/2024/07/16/une-convention-citoyenne-sur-la-democratie-permettrait-de-reformer-notre-constitution-et-d-inventer-une-democratie-plus-participative_6251120_3232.html" target="_blank" rel="noopener">
        <div class="resource-source">Le Monde · Tribune · Réservé aux abonnés</div>
        <div class="resource-title">Une convention citoyenne sur la démocratie permettrait de réformer notre Constitution</div>
        <span class="resource-arrow">Lire la tribune →</span>
      </a>
      <a class="resource" href="https://www.sciencespo.fr/cevipof/sites/sciencespo.fr.cevipof/files/SAB_FC_AssCitConfPol_janv2026.pdf" target="_blank" rel="noopener">
        <div class="resource-source">CEVIPOF · Étude</div>
        <div class="resource-title">Les assemblées citoyennes, un espoir sur fond de défiance politique</div>
        <span class="resource-arrow">Lire le PDF →</span>
      </a>
      <a class="resource" href="https://democis.fr/ " target="_blank" rel="noopener">
        <div class="resource-source">Université de Lille</div>
        <div class="resource-title">Le projet de recherche DemoCIS, lauréat France 2030</div>
        <span class="resource-arrow">Découvrir le projet →</span>
      </a>
    </div>
  </div>
</section>

<!-- ============================ FOOTER ============================ -->
<?php layout_footer(true); ?>

<!-- ============================ POP-IN ACTUALITÉ ============================ -->
<?php layout_actu_modal(); ?>

<script src="./assets/site.js"></script>
<script src="./assets/actus.js"></script>

<!-- =========================================================================
   BREVO — variables de config + script principal
   ========================================================================= -->
<script>
  window.REQUIRED_CODE_ERROR_MESSAGE = 'Veuillez choisir un code pays';
  window.LOCALE = 'fr';
  window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE =
    "Les informations que vous avez fournies ne sont pas valides. Veuillez vérifier le format du champ et réessayer.";
  window.REQUIRED_ERROR_MESSAGE = "Vous devez renseigner ce champ.";
  window.GENERIC_INVALID_MESSAGE =
    "Les informations que vous avez fournies ne sont pas valides. Veuillez vérifier le format du champ et réessayer.";
  window.INVALID_NUMBER =
    "Les informations que vous avez fournies ne sont pas valides. Veuillez vérifier le format du champ et réessayer.";
  window.INVALID_DATE = "Veuillez saisir une date valide";
  window.REQUIRED_MULTISELECT_MESSAGE = "Veuillez choisir au moins une option";
  window.translation = {
    common: {
      selectedList: '{quantity} liste sélectionnée',
      selectedLists: '{quantity} listes sélectionnées',
      selectedOption: '{quantity} sélectionné',
      selectedOptions: '{quantity} sélectionnés',
    }
  };
  var AUTOHIDE = Boolean(0);
</script>
<script defer src="https://sibforms.com/forms/end-form/build/main.js"></script>

<!-- =========================================================================
   PONT : formulaire custom → formulaire Brevo caché
   =========================================================================
   Quand l'utilisateur clique "Je m'inscris" sur le formulaire visible :
   1. On copie l'email dans le champ caché #EMAIL (celui que Brevo écoute)
   2. On simule un clic sur le bouton submit du formulaire Brevo
   3. On observe les divs #success-message / #error-message pour afficher
      les messages inline dans le formulaire custom
   ========================================================================= -->
<script>
(function () {
  const btn     = document.getElementById('newsletter-submit-btn');
  const input   = document.getElementById('EMAIL-visible');
  const sibInput = document.getElementById('EMAIL');
  const sibForm  = document.getElementById('sib-form');
  const customForm = document.getElementById('newsletter-custom-form');
  const successEl = document.getElementById('newsletter-success');
  const errorEl   = document.getElementById('newsletter-error');

  if (!btn || !input || !sibInput || !sibForm) return;

  btn.addEventListener('click', function () {
    const email = input.value.trim();

    // Validation basique côté client
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errorEl.style.display = 'block';
      successEl.style.display = 'none';
      errorEl.textContent = '✗ Veuillez saisir une adresse email valide.';
      return;
    }

    // Copier l'email dans le champ Brevo caché
    sibInput.value = email;

    // Observer les messages de retour Brevo
    const sibSuccess = document.getElementById('success-message');
    const sibError   = document.getElementById('error-message');

    const observer = new MutationObserver(function () {
      const successVisible =
        sibSuccess && (
          sibSuccess.style.display === 'block' ||
          sibSuccess.classList.contains('sib-form-message-panel--active')
        );
      const errorVisible =
        sibError && (
          sibError.style.display === 'block' ||
          sibError.classList.contains('sib-form-message-panel--active')
        );

      if (successVisible) {
        successEl.style.display = 'block';
        errorEl.style.display   = 'none';
        customForm.querySelector('.newsletter-input').style.opacity = '0.4';
        customForm.querySelector('.newsletter-input').style.pointerEvents = 'none';
        observer.disconnect();
      } else if (errorVisible) {
        errorEl.style.display   = 'block';
        successEl.style.display = 'none';
        observer.disconnect();
      }
    });

    if (sibSuccess) observer.observe(sibSuccess, { attributes: true, attributeFilter: ['class', 'style'] });
    if (sibError)   observer.observe(sibError,   { attributes: true, attributeFilter: ['class', 'style'] });

    // Déclencher la soumission du formulaire Brevo caché via le bouton dédié
    const sibSubmit = document.getElementById('sib-submit-btn');
    if (sibSubmit) {
      sibSubmit.click();
    }

    // Sécurité : si Brevo ne répond pas en 5s, afficher un message générique
    setTimeout(function () {
      observer.disconnect();
      if (successEl.style.display === 'none' && errorEl.style.display === 'none') {
        successEl.style.display = 'block';
        successEl.textContent   = '✓ Votre demande a été envoyée.';
      }
    }, 5000);
  });

  // Permettre la soumission via la touche Entrée
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') btn.click();
  });
})();
</script>

</body>
</html>
