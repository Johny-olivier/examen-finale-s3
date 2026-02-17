<?php

declare(strict_types=1);

if (!function_exists('vue_echapper')) {
    function vue_echapper($valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('vue_formater_nombre')) {
    function vue_formater_nombre(float $valeur): string
    {
        return number_format($valeur, 2, ',', ' ');
    }
}

if (!function_exists('vue_formater_quantite')) {
    function vue_formater_quantite(float $valeur, ?string $unite = null): string
    {
        $quantite = vue_formater_nombre($valeur);
        $libelleUnite = trim((string) ($unite ?? ''));
        if ($libelleUnite === '') {
            return $quantite;
        }

        return $quantite . ' ' . $libelleUnite;
    }
}

if (!function_exists('vue_formater_montant_ar')) {
    function vue_formater_montant_ar(float $valeur): string
    {
        return vue_formater_nombre($valeur) . ' Ar';
    }
}

if (!function_exists('vue_formater_prix_unitaire_ar')) {
    function vue_formater_prix_unitaire_ar(float $valeur): string
    {
        return vue_formater_nombre($valeur) . ' Ar';
    }
}

if (!function_exists('vue_formater_date_humaine')) {
    function vue_formater_date_humaine($valeur, bool $inclureHeure = true): string
    {
        $date = trim((string) $valeur);
        if ($date === '') {
            return '-';
        }

        $horodatage = strtotime($date);
        if ($horodatage === false) {
            return $date;
        }

        $mois = [
            1 => 'janv',
            2 => 'fev',
            3 => 'mars',
            4 => 'avr',
            5 => 'mai',
            6 => 'juin',
            7 => 'juil',
            8 => 'aout',
            9 => 'sept',
            10 => 'oct',
            11 => 'nov',
            12 => 'dec',
        ];

        $jour = date('d', $horodatage);
        $numeroMois = (int) date('n', $horodatage);
        $annee = date('Y', $horodatage);

        $resultat = $jour . ' ' . ($mois[$numeroMois] ?? date('m', $horodatage)) . ' ' . $annee;

        if ($inclureHeure === true) {
            $heure = date('H:i', $horodatage);
            if ($heure !== '00:00') {
                $resultat .= ' ' . $heure;
            }
        }

        return $resultat;
    }
}

if (!function_exists('vue_meta_statut_besoin')) {
    /**
     * @return array{libelle:string,classe:string}
     */
    function vue_meta_statut_besoin(string $status): array
    {
        $statutNormalise = trim(strtolower($status));

        if ($statutNormalise === 'dispatche') {
            return [
                'libelle' => 'Dispatche',
                'classe' => 'text-bg-success',
            ];
        }

        if ($statutNormalise === 'achete') {
            return [
                'libelle' => 'Achete',
                'classe' => 'text-bg-info',
            ];
        }

        return [
            'libelle' => 'Non dispatche',
            'classe' => 'text-bg-warning',
        ];
    }
}
