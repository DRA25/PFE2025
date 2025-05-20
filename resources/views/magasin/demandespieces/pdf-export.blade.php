<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Demandes de Pièces</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            text-align: center;
        }

        .t1 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: 'Inter', sans-serif;
        }

        .t1, .ttd {
            border: 1px solid black;
        }

        .ttd {
            text-align: center;
        }

        img {
            width: 90px;
        }

        h1 {
            color: #042B62FF;
            text-align: center;
            font-size: 1.5rem;
            margin-top: 20px;
        }

        .t2 {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Inter', sans-serif;
            margin-bottom: 100px;
        }

        .t2, th, td {
            border: 1px solid #000000;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-size: 0.9rem;
        }

        .text-center {
            text-align: center;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<table class="t1">
    <tr>
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Company Logo"></td>
        <td class="ttd" style="width:70%"><h1>Liste des Demandes de Pièces</h1></td>
        <td style="width:20%; padding: 0;"></td>
    </tr>
</table>

<p class="text-center">Généré le: {{ now()->format('d/m/Y H:i') }}</p>
<h1>

    @if($etat)
        Filtré par État: {{ $etat }}  {{-- Use $etat here --}}
    @endif
</h1>
<table class="t2">
    <thead>
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>État</th>
        <th>Quantité</th>
        <th>Pièce</th>
        <th>Origine</th>
        <th>Centre</th>
    </tr>
    </thead>
    <tbody>
    @foreach($demandes as $demande)
        <tr>
            <td>{{ $demande->id_dp }}</td>
            <td>{{ $demande->date_dp }}</td>
            <td>{{ $demande->etat_dp }}</td>
            <td>{{ $demande->qte_demandep }}</td>
            <td>{{ $demande->piece->nom_piece ?? 'N/A' }}</td>
            <td>
                @if($demande->magasin)
                    Magasin - {{ $demande->magasin->adresse_magasin }}
                @elseif($demande->atelier)
                    Atelier - {{ $demande->atelier->adresse_atelier }}
                @else
                    N/A
                @endif
            </td>
            <td>
                {{ $demande->magasin->centre->id_centre ?? ($demande->atelier->centre->id_centre ?? 'N/A') }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


</body>
</html>
