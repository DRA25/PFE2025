<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État Trimestriel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: landscape; /* Set page orientation to landscape */
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .t1 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
            color: #042B62;
            text-align: center;
            font-size: 1.2rem;
            margin-top: 20px;
        }

        .info-section {
            text-align: left;
            margin-bottom: 15px;
            padding: 0 10px;
        }

        .info-item {
            margin-bottom: 3px;
            font-size: 0.85rem;
            color: #333;
        }

        .info-item strong {
            color: #042B62;
        }

        .trimestre-info {
            font-weight: bold;
            padding-left: 10px;
            margin-bottom: 10px;
        }

        table.t2 {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .t2, .t2 th, .t2 td {
            border: 1px solid #000;
        }

        .t2 th, .t2 td {
            padding: 5px;
            text-align: left;
            font-size: 0.8rem;
        }

        .t2 th {
            background-color: #f2f2f2;
            font-size: 0.85rem;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .footer-text {
            font-size: 0.7rem;
            color: #666;
            margin-top: 20px;
            text-align: center;
        }

        .divider {
            border-top: 1px solid #ddd;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<table class="t1">
    <tr>
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Logo"></td>
        <td class="ttd" style="width:70%"><h1>ÉTAT TRIMESTRIEL</h1></td>
        <td style="width:20%; font-size: 0.8rem;">{{ $currentDate }}</td>
    </tr>
</table>

<div class="info-section">
    <div class="info-item"><strong>Branche Carburants</strong></div>
    <div class="info-item"><strong>D.{{ strtoupper($centreType) }} DÉPT APPRO</strong></div>
    <div class="info-item"><strong>A MONSIEUR LE DIRECTEUR {{ strtoupper($centreType) }}</strong></div>

    <div class="info-item">Trimestre : {{ $trimestre }}</div>
</div>

<table class="t2">
    <thead>
    <tr>
        <th colspan="9" style="text-align: center; background-color: #f2f2f2;">
            Centre {{ $centreType }} ({{ $centreCode }})
        </th>
    </tr>
    <tr>
        <th style="width: 3%">Item</th>
        <th style="width: 25%">Libellé</th>
        <th style="width: 20%">Compte Charge</th>
        <th style="width: 8%">Date</th>
        <th style="width: 15%">Fournisseur</th>
        <th style="width: 5%">cds</th>
        <th style="width: 8%">FOURNITURE CONSOMMABLE</th>
        <th style="width: 8%">TRAVAUX ET PRESTATIONS DE SERVICES</th>
        <th style="width: 8%">HÉBERGEMENT, RESTAURATION, TRANSPORT, ETC.</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td class="text-center">{{ $item['item'] }}</td>
            <td>{{ $item['libelle'] }}</td>
            <td>{{ $item['compte_charge'] }}</td>
            <td class="text-center">{{ $item['date'] }}</td>
            <td>{{ $item['fournisseur'] }}</td>
            <td class="text-center">{{ $item['cds'] }}</td>
            <td class="text-right">{{ $item['fourniture_consommable'] }}</td>
            <td class="text-right">{{ $item['travaux_prestations'] }}</td>
            <td class="text-right">{{ $item['autres'] }}</td>
        </tr>
    @endforeach
    <tr class="total-row">
        <td colspan="6">Sous Total</td>
        <td class="text-right">{{ $totalFourniture }}</td>
        <td class="text-right">{{ $totalTravaux }}</td>
        <td class="text-right">{{ $totalAutres }}</td>
    </tr>
    <tr class="total-row">
        <td colspan="6">Total</td>
        <td colspan="3" class="text-right">{{ $grandTotal }}</td>
    </tr>
    </tbody>
</table>

<div class="divider"></div>
<p class="footer-text">Généré le: {{ $currentDate }}</p>
</body>
</html>
