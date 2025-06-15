<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Commande n° {{ $boncommande['n_bc'] }}</title>
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

        h2 {
            color: #042B62FF;
            text-align: center;
            font-size: 1.2rem;
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .t2 {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Inter', sans-serif;
            margin-bottom: 40px;
        }

        .t2, .t2 th, .t2 td {
            border: 1px solid #000000;
        }

        .t2 th, .t2 td {
            padding: 8px;
            text-align: left;
        }

        .t2 th {
            background-color: #f2f2f2;
            font-size: 0.9rem;
        }

        .text-center {
            text-align: center;
        }

        .info-section {
            text-align: left;
            margin-bottom: 20px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .info-item {
            margin-bottom: 5px;
            font-size: 1rem;
            color: #333;
        }

        .info-item strong {
            color: #042B62FF;
        }

        .footer-text {
            font-size: 0.8rem;
            color: #666;
            margin-top: 30px;
            text-align: center;
        }

        .total-amount {
            text-align: right;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 20px;
            padding-right: 10px;
            color: #042B62FF;
        }

        .divider {
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<table class="t1">
    <tr>
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Company Logo"></td>
        <td class="ttd" style="width:70%"><h1>Détails du Bon de Commande n° {{ $boncommande['n_bc'] }}</h1></td>
        <td style="width:20%; padding: 0;"></td>
    </tr>
</table>

<div class="info-section">
    <div class="info-item"><strong>Numéro BC:</strong> {{ $boncommande['n_bc'] }}</div>
    <div class="info-item"><strong>Date du Bon de Commande:</strong> {{ \Carbon\Carbon::parse($boncommande['date_bc'])->format('d/m/Y') }}</div>
    <div class="info-item"><strong>Nombre de Pièces Commandées:</strong> {{ count($boncommande['pieces']) }}</div>
    <div class="info-item"><strong>Nombre de Prestations Commandées:</strong> {{ count($boncommande['prestations']) }}</div>
    <div class="info-item"><strong>Nombre de Charges Commandées:</strong> {{ count($boncommande['charges']) }}</div>
</div>

<div class="divider"></div>

<h2>Pièces Commandées</h2>
@if(count($boncommande['pieces']) > 0)
    <table class="t2">
        <thead>
        <tr>
            <th>ID Pièce</th>
            <th>Nom Pièce</th>
            <th>Quantité</th>

        </tr>
        </thead>
        <tbody>
        @foreach($boncommande['pieces'] as $piece)
            <tr>
                <td>{{ $piece['id_piece'] }}</td>
                <td>{{ $piece['nom_piece'] }}</td>
                <td>{{ $piece['qte_commandep'] }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Aucune pièce n'est commandée pour ce bon.</p>
@endif

<div class="divider"></div>

<h2>Prestations Commandées</h2>
@if(count($boncommande['prestations']) > 0)
    <table class="t2">
        <thead>
        <tr>
            <th>ID Prestation</th>
            <th>Nom Prestation</th>
            <th>Quantité</th>

        </tr>
        </thead>
        <tbody>
        @foreach($boncommande['prestations'] as $prestation)
            <tr>
                <td>{{ $prestation['id_prest'] }}</td>
                <td>{{ $prestation['nom_prest'] }}</td>
                <td>{{ $prestation['qte_commandepr'] }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Aucune prestation n'est commandée pour ce bon.</p>
@endif

<div class="divider"></div>

<h2>Charges Commandées</h2>
@if(count($boncommande['charges']) > 0)
    <table class="t2">
        <thead>
        <tr>
            <th>ID Charge</th>
            <th>Nom Charge</th>
            <th>Quantité</th>

        </tr>
        </thead>
        <tbody>
        @foreach($boncommande['charges'] as $charge)
            <tr>
                <td>{{ $charge['id_charge'] }}</td>
                <td>{{ $charge['nom_charge'] }}</td>
                <td>{{ $charge['qte_commandec'] }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Aucune charge n'est commandée pour ce bon.</p>
@endif

<div class="divider"></div>




<p class="footer-text">Généré le: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

</body>
</html>
