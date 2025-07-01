<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Dérégation</title>
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

        /* Adjusted for the new layout, assuming a placeholder image for Naftal logo */
        .ttd img {
            width: 90px;
            /* If you have a real image path, you might need to adjust this */
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

        .footer-signatures {
            margin-top: 50px;
            text-align: right;
            padding-right: 10px;
        }

        .footer-signatures p {
            margin: 10px 0;
            font-size: 0.9rem;
        }

        .date-info {
            text-align: left;
            padding-left: 10px;
            margin-top: 20px;
            font-size: 0.85rem;
        }

        .divider {
            border-top: 1px solid #ddd;
            margin: 15px 0;
        }

        #signtab{
            width: 100%;
            margin-top: 50px;
            margin-left: 100px;

        }

    </style>
</head>
<body>
<table class="t1">
    <tr>
        {{-- You'll need to replace 'images/Naftal.png' with the actual path to your logo if you have one --}}
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Logo Naftal"></td>
        <td class="ttd" style="width:70%"><h1>Demande de Dérogation</h1></td>
        <td style="width:20%; font-size: 0.8rem;">Date: {{ $currentDate }}</td>
    </tr>
</table>

<div class="info-section">
    <div class="info-item"><strong>A MONSIEUR LE DIRECTEUR {{ strtoupper($centreType) }}</strong></div>
    <div class="info-item">Objet: <strong>demande de dérogation</strong></div>
    <div class="info-item">Nous sollicitons votre accord pour les dépenses suivantes sur caisse regie N({{ $reference }})</div>
</div>

<table class="t2">
    <thead>
    <tr>
        <th style="width: 3%">Item</th>
        <th style="width: 25%">Libellé</th>
        <th style="width: 20%">Fournisseur</th>
        <th style="width: 5%">Cds</th>
        <th style="width: 15%">FOURNITURE CONSOMMABLE</th>
        <th style="width: 17%">TRAVAUX ET PRESTATIONS DE SERVICES</th>
        <th style="width: 15%">HÉBERGEMENT, RESTAURATION, TRANSPORT, ETC.</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td class="text-center">{{ $item['item'] }}</td>
            <td>{{ $item['libelle'] }}</td>
            <td>{{ $item['fournisseur'] }}</td>
            <td class="text-center">{{ $item['cds'] }}</td>
            <td class="text-right">{{ $item['fourniture_consommable'] }}</td>
            <td class="text-right">{{ $item['travaux_prestations'] }}</td>
            <td class="text-right">{{ $item['autres'] }}</td>
        </tr>
    @endforeach
    <tr class="total-row">
        <td colspan="4"><strong>Sous Total</strong></td>
        <td class="text-right"><strong>{{ $totalFourniture }}</strong></td>
        <td class="text-right"><strong>{{ $totalTravaux }}</strong></td>
        <td class="text-right"><strong>{{ $totalAutres }}</strong></td>
    </tr>
    <tr class="total-row">
        <td colspan="4"><strong>Total Global</strong></td>
        <td colspan="3" class="text-right"><strong>{{ $grandTotal }}</strong></td>
    </tr>
    </tbody>
</table>





<table id="signtab">
    <tr>
        <td style="width: 200px">Chef unité</td>
        <td style="width: 200px">Chef Département</td>
        <td style="width: 200px">DIRECTEUR {{ strtoupper($centreType) }}</td>
    </tr>
</table>

</body>
</html>
