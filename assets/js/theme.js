(function () {
    'use strict';

    var CLE_THEME = 'bngrc_theme';
    var elementRacine = document.documentElement;
    var boutonTheme = document.getElementById('bouton-theme');

    var obtenirThemeSysteme = function () {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    };

    var obtenirThemeInitial = function () {
        var themeSauvegarde = null;
        try {
            themeSauvegarde = localStorage.getItem(CLE_THEME);
        } catch (erreur) {
            themeSauvegarde = null;
        }
        if (themeSauvegarde === 'dark' || themeSauvegarde === 'light') {
            return themeSauvegarde;
        }
        return obtenirThemeSysteme();
    };

    var appliquerTheme = function (theme) {
        elementRacine.setAttribute('data-theme', theme);

        if (!boutonTheme) {
            return;
        }

        var icone = boutonTheme.querySelector('i');
        var texte = boutonTheme.querySelector('span');
        var estSombre = theme === 'dark';

        boutonTheme.setAttribute('aria-pressed', estSombre ? 'true' : 'false');

        if (icone) {
            icone.className = estSombre ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }

        if (texte) {
            texte.textContent = estSombre ? 'Mode clair' : 'Mode sombre';
        }
    };

    var themeActuel = obtenirThemeInitial();
    appliquerTheme(themeActuel);

    if (!boutonTheme) {
        return;
    }

    boutonTheme.addEventListener('click', function () {
        themeActuel = elementRacine.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        try {
            localStorage.setItem(CLE_THEME, themeActuel);
        } catch (erreur) {
            // Pas de persistence si localStorage indisponible.
        }
        appliquerTheme(themeActuel);
    });
})();
