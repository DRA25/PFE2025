<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de Sortie - Tous les DRAs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            text-align: center;
            font-size: 0.8rem; /* Reduced base font size */
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
            font-size: 1.2rem; /* Reduced h1 font size */
            margin-top: 20px;
        }

        h2 {
            color: #042B62FF;
            text-align: center;
            font-size: 1rem; /* Reduced h2 font size */
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
            padding: 5px; /* Reduced padding */
            text-align: left;
            font-size: 0.8rem; /* Reduced table cell font size */
        }

        .t2 th {
            background-color: #f2f2f2;
            font-size: 0.85rem; /* Slightly larger for table headers */
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
            margin-bottom: 3px; /* Reduced margin */
            font-size: 0.85rem; /* Reduced info-item font size */
            color: #333;
        }

        .info-item strong {
            color: #042B62FF;
        }

        .footer-text {
            font-size: 0.7rem; /* Reduced footer font size */
            color: #666;
            margin-top: 20px; /* Reduced margin-top */
            text-align: center;
        }

        .total-amount {
            text-align: right;
            font-size: 0.8rem;
            font-weight: bold;
            margin-top: 20px;
            padding-right: 10px;
            color: #042B62FF;
        }

        .divider {
            border-top: 1px solid #ddd;
            margin: 15px 0; /* Reduced margin */
        }

        /* Specific styles for the DRA table */
        .dra-table th, .dra-table td {
            text-align: left; /* Override default center for table cells if needed */
        }
        .dra-total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        /* Adjusting specific column widths */
        .dra-table th:nth-child(7), /* Nombre Pièces header */
        .dra-table td:nth-child(7) { /* Nombre Pièces data */
            width: 8%; /* Adjust as needed, making it smaller */
            text-align: center; /* Keep content centered for numbers */
        }

        .dra-table th:nth-child(8), /* Total header */
        .dra-table td:nth-child(8) { /* Total data */
            width: 12%; /* Adjust as needed, making it larger */
            text-align: right; /* Keep content right-aligned for numbers */
        }
    </style>
</head>
<body>
<table class="t1">
    <tr>
        <td class="ttd" style="width:10%"><img src="{{ public_path('images/Naftal.png') }}" alt="Company Logo"></td>
        <td class="ttd" style="width:70%"><h1>BROUILLARD CAISSE REGIE</h1></td>
        <td style="width:20%; padding: 0;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i')}}</td>
    </tr>
</table>

<div class="info-section">
    <div class="info-item"><strong>Branche Carburants</strong></div>
    <div class="info-item"><strong>Direction {{ $centre_type }}</strong></div>
    <div class="info-item"><strong>Code: 1{{ $id_centre }}</strong></div>
</div>

<div class="divider"></div>

<table class="t2 dra-table">
    <thead>
    <tr>
        <th>N° DRA</th>
        <th>Date création</th>
        <th>Libellé</th>
        <th>Montant</th>
        <th>TVA</th>
        <th>Droit Timbre</th>
        <th style="width: 8%;">Nombre Pièces</th> <th style="width: 12%;">Total</th> </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr class="@if($item['is_total']) dra-total-row @endif">
            <td>{{ $item['n_dra'] }}</td>
            <td>{{ $item['date_creation'] }}</td>
            <td>{{ $item['libelle'] }}</td>
            <td class="text-right">{{ $item['montant'] }}</td>
            <td class="text-right">{{ $item['tva'] }}</td>
            <td class="text-right">{{ $item['droit_timbre'] }}</td>
            <td class="text-center">{{ $item['nombre_piece'] }}</td>
            <td class="text-right">{{ $item['total'] }}</td>
        </tr>
    @endforeach
    <tr class="dra-total-row">
        <td colspan="3">TOTAL GÉNÉRAL</td>
        <td class="text-right">{{ $totalMontant }}</td>
        <td class="text-right">{{ $totalTVA }}</td>
        <td class="text-right">{{ $totalDroitTimbre }}</td>
        <td class="text-center">{{ $items->where('is_total', false)->sum('nombre_piece') }}</td>
        <td class="text-right">{{ $totalGeneral }}</td>
    </tr>
    </tbody>
</table>

<div class="divider"></div>

<div class="info-section">
    <div class="info-item"><strong>SOLDE PÉRIODE / REPORT PÉRIODE:</strong> {{ $totalGeneral }}</div>
    <div class="info-item">NB: LE SOLDE FIN DE PÉRIODE DOIT ÊTRE DÉBITEUR OU NUL EN AUCUN CAS IL PRÉSENTE UN SOLDE NÉGATIF</div>
    <div class="info-item">S'IL S'AGIT DE PLUSIEUR PAGES IL Y'A LIEU D'INDIQUER LE REPORT DES PAGES</div>
</div>

<p class="footer-text">Généré le: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

</body>
</html>
