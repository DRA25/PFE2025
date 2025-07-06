<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BROUILLARD CAISSE REGIE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: landscape;
            margin: 20px;
            margin-bottom: 50px; /* Extra space for QR code if it were on every page, adjust if needed for last page only */
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            text-align: center;
            margin: 0;
            padding: 0;
            position: relative;
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
            color: #042B62FF;
            text-align: center;
            font-size: 1.2rem;
            margin-top: 20px;
        }

        h2 {
            color: #042B62FF;
            text-align: left;
            font-size: 1rem;
            margin-top: 30px;
            margin-bottom: 10px;
            padding-left: 10px;
            padding-right: 10px;
        }

        h3 {
            color: #042B62FF;
            text-align: left;
            font-size: 0.9rem;
            margin-bottom: 5px;
            padding-left: 10px;
            padding-right: 10px;
        }

        p.info-period {
            color: #042B62FF;
            text-align: left;
            font-size: 0.85rem;
            margin-bottom: 3px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .t2 {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 40px;
        }

        .t2, .t2 th, .t2 td {
            border: 1px solid #000000;
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .info-section {
            text-align: left;
            margin-bottom: 20px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .info-item {
            margin-bottom: 3px;
            font-size: 0.85rem;
            color: #333;
        }

        .info-item strong {
            color: #042B62FF;
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

        .dra-table th, .dra-table td {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        /* QR Code Styles - Modified */
        .qr-code-container {
            /* Initially hide the QR code container */
            display: none;
        }

        /* This is the key: only display the QR code when it's inside the last-page-content */
        .last-page-content .qr-code-container {
            display: block; /* Make it visible */
            position: absolute; /* Position relative to the last page's content flow */
            right: 20px;
            bottom: 20px;
            text-align: center;
            width: 150px;
        }


        .qr-code-container img {
            width: 150px;
            height: 150px;
        }

        .qr-code-label {
            font-size: 7px;
        }
    </style>
</head>
<body>

<table class="t1">
    <tr>
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Company Logo"></td>
        <td class="ttd" style="width:70%"><h1>BROUILLARD CAISSE REGIE</h1></td>
        <td style="width:20%; padding: 0; font-size: 0.8rem;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i')}}</td>
    </tr>
</table>

<div class="info-section">
    <div class="info-item"><strong>Branche Carburants</strong></div>
    <div class="info-item"><strong>Direction {{ $centre_type }}</strong></div>
    <div class="info-item"><strong>Code: 1{{ $centre_code }}</strong></div>
</div>

<div class="info-section">
    <div class="info-item">Période: Du {{ $periode_debut }} à {{ $periode_fin }}</div>
    <div class="info-item">Trimestre: {{ $trimestre }}</div>
</div>

<div class="divider"></div>

<table class="t2 dra-table">
    <thead>
    <tr>
        <th>N° DRA</th>
        <th>N° Bon de petite caisse</th>
        <th>Date du bon</th>
        <th>Libellé</th>
        <th>Fournisseur</th>
        <th class="text-right">Encaissement</th>
        <th class="text-right">Decaissement</th>
        <th>OBS</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr class="{{ $item['is_total'] ? 'total-row' : '' }}">
            <td class="text-center">{{ $item['n_dra'] }}</td>
            <td class="text-center">{{ $item['n_bon'] }}</td>
            <td class="text-center">{{ $item['date_bon'] }}</td>
            <td>{{ $item['libelle'] }}</td>
            <td class="text-center">{{ $item['fournisseur'] }}</td>
            <td class="text-right">{{ $item['encaissement'] }}</td>
            <td class="text-right">{{ $item['decaissement'] }}</td>
            <td>{{ $item['obs'] }}</td>
        </tr>
    @endforeach
    <tr class="total-row">
        <td colspan="5" class="text-right">TOTAL PÉRIODE </td>
        <td class="text-right">{{ $totalEncaissement }}</td>
        <td class="text-right">{{ $totalDecaissement }}</td>
        <td></td>
    </tr>
    <tr class="total-row">
        <td colspan="5" class="text-right">SOLDE PÉRIODE / REPORT PÉRIODE </td>
        <td class="text-right"> {{ $calculatedResult }}</td>
        <td></td>
        <td></td>
    </tr>
    </tbody>
</table>

<div class="divider"></div>

<div class="info-section">
    <div class="info-item">NB: LE SOLDE FIN DE PÉRIODE DOIT ÊTRE DÉBITEUR OU NUL EN AUCUN CAS IL PRÉSENTE UN SOLDE NÉGATIF</div>
    <div class="info-item">S'IL S'AGIT DE PLUSIEUR PAGES IL Y'A LIEU D'INDIQUER LE REPORT DES PAGES</div>
</div>

<p class="footer-text">Généré le: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

<div class="last-page-content">
    <div class="qr-code-container">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
        <p class="qr-code-label">Scan pour vérification</p>
    </div>
</div>

</body>
</html>
