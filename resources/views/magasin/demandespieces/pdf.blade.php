<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Pièce #{{ $demande->id_dp }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { color: #042B62; margin-top: 80px; margin-bottom: 40px; }
        .info { margin-bottom: 15px; }
        .label { font-weight: bold; }
        .logo-container {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .logo-container img {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>

<div class="logo-container ">
    <img src="{{ public_path('images/Naftal.png') }}" alt="Company Logo">
</div>

<h1>Détails de la Demande de Pièce N°{{ $demande->id_dp }}</h1>

<div class="info">
    <span class="label">ID:</span> {{ $demande->id_dp }}
</div>

<div class="info">
    <span class="label">Date de Demande:</span> {{ $demande->date_dp }}
</div>

<div class="info">
    <span class="label">Origine:</span>
    @if($demande->magasin)
        Magasin - {{ $demande->magasin->adresse_magasin }}
    @elseif($demande->atelier)
        Atelier - {{ $demande->atelier->adresse_atelier }}
    @else
        N/A
    @endif
</div>

<div class="info">
    <span class="label">Centre:</span>
    {{ $demande->magasin->centre->id_centre ?? $demande->atelier->centre->id_centre ?? 'N/A' }}
</div>

<div class="info">
    <span class="label">Quantité:</span> {{ $demande->qte_demandep }}
</div>

<div class="info">
    <span class="label">Nom de la Pièce:</span> {{ $demande->piece->nom_piece ?? 'N/A' }}
</div>

<div class="info">
    <span class="label">État:</span> {{ $demande->etat_dp }}
</div>

</body>
</html>
