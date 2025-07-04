<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereau des Opérations Diverses</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: landscape;
            margin: 2mm;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 0.75rem;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.4;
        }

        .header-main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-main-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
        }

        .header-main-table .logo-cell {
            width: 10%;
            text-align: center;
        }

        .header-main-table .logo-cell img {
            width: 70px;
            height: auto;
        }

        .header-main-table .title-cell {
            width: 70%;
            text-align: center;
        }

        .header-main-table .title-cell h1 {
            color: #042B62;
            font-size: 1.3rem;
            margin: 0;
        }

        .header-main-table .title-cell h2 {
            font-size: 0.9rem;
            margin: 3px 0 0;
            color: #555;
            font-weight: normal;
        }

        .header-main-table .info-cell {
            width: 20%;
            font-size: 0.65rem;
            text-align: right;
            line-height: 1.5;
        }

        .info-block {
            text-align: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
            padding: 0 5px;
        }

        .info-block strong {
            color: #042B62;
        }

        .summary-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .summary-info-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #042B62;
        }

        .summary-info-table strong {
            color: #000;
        }

        .operations-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: avoid;
        }

        .operations-table th,
        .operations-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 0.6rem;
            vertical-align: middle;
        }

        .operations-table th {
            background-color: #e6e6e6;
            font-weight: bold;
            text-transform: uppercase;
            color: #042B62;
        }

        .operations-table .rotate-header {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            white-space: nowrap;
            padding: 4px 2px;
            font-size: 0.55rem;
        }

        .operations-table .libelle {
            text-align: left;
            font-size: 0.65rem;
        }

        .amount {
            text-align: right;
            font-weight: 600;
            color: #333;
        }

        .bottom-layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .bottom-layout-table td {
            vertical-align: top;
            padding: 0;
            border: 1px solid #000;
            box-sizing: border-box;
        }

        .financial-summary,
        .certification-signatures {
            padding: 8px;
            box-sizing: border-box;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .financial-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-summary td {
            border: none;
            padding: 3px 0;
            font-size: 0.65rem;
        }

        .financial-summary td:first-child {
            text-align: left;
            width: 70%;
        }

        .financial-summary td:last-child {
            text-align: right;
            font-weight: bold;
            color: #042B62;
        }

        .certification-text {
            font-size: 0.7rem;
            margin-bottom: 10px;
            line-height: 1.3;
            font-style: italic;
            color: #555;
            text-align: center;
        }

        .signature-grid {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 8px;
            flex-wrap: nowrap;
        }

        .signature-block {
            text-align: center;
            font-size: 0.65rem;
            line-height: 1.5;
        }

        .signature-block strong {
            color: #042B62;
            font-size: 0.75rem;
        }

        .signature-line {
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .page-break {
            page-break-before: always;
        }

        .qr-code-container {
            position: absolute;
            left: 20px;
            bottom: 20px;
            text-align: center;
            width: 100px;
            z-index: 100;
        }

        .qr-code-container img {
            width: 80px;
            height: 80px;
        }

        .qr-code-label {
            font-size: 7px;
            margin-top: 2px;
            color: #555;
        }

        /* New styles for page layout */
        .page-container {
            position: relative;
            min-height: 95vh;
            display: flex;
            flex-direction: column;
        }

        .content-section {
            flex: 1;
        }

        .footer-section {
            margin-top: auto;
        }

        .page-wrapper {
            page-break-after: always;
        }

        .page-wrapper:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>

@php
    // Calculate how many rows we can fit per page
    $rowsPerPage = 25; // Adjust based on your testing
    $totalRows = count($operations);
    $totalPages = ceil($totalRows / $rowsPerPage);
@endphp

@for($page = 1; $page <= $totalPages; $page++)
    <div class="page-container @if($page > 1) page-break @endif">
        {{-- HEADER --}}
        <table class="header-main-table">
            <tr>
                <td class="logo-cell"><img src="{{ public_path('images/Naftal.png') }}" alt="Logo Naftal"></td>
                <td class="title-cell">
                    <h1>BORDEREAU DES OPERATIONS DIVERSES</h1>
                    <h2>(DÉPENSES SUR RÉGIES / ACCRÉDITIFS)</h2>
                </td>
                <td class="info-cell">
                    <div><strong>Date:</strong> {{ $currentDate }}</div>
                </td>
            </tr>
        </table>

        <div class="info-block">
            <p><strong>DIRECTION {{ strtoupper($centreType) }}</strong></p>
        </div>

        <table class="summary-info-table">
            <tr>
                <td>CODE CARTE <strong> |1ère CARTE : 50 </strong> <strong> |2ème CARTE : 51 </strong></td>
                <td>UNITE COMPT. <strong>652</strong></td>
                <td>PERIODE COMPT. <strong>{{ \Carbon\Carbon::parse($dra->date_creation)->format('m y') }}</strong></td>
                <td>CODE JOURNAL <strong>08</strong></td>
                <td>N° BORDEREAU <strong>{{ $dra->n_dra }}</strong></td>
            </tr>
        </table>

        {{-- CONTENT SECTION --}}
        <div class="content-section">
            {{-- OPERATIONS TABLE --}}
            <table class="operations-table">
                <thead>
                <tr>
                    <th class="rotate-header">PAGE</th>
                    <th class="rotate-header">LIGNES</th>
                    <th style="width: 10%;">N° D'ENREGISTREMENT</th>
                    <th style="width: 12%;">REFERENCE DU DOCUMENT D'ENGAGEMENT</th>
                    <th style="width: 10%;">COMPTES GENERAUX OU CODES TIERS</th>
                    <th style="width: 10%;">COMPTES ANALYTIQUES</th>
                    <th style="width: 12%;">DEBIT (MONTANT A)</th>
                    <th style="width: 12%;">CREDIT (MONTANT B)</th>
                    <th style="width: 8%;">DEVISES (MONTANT C)</th>
                    <th style="width: 24%;">LIBELLE</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp

                @foreach(array_slice($operations->toArray(), ($page - 1) * $rowsPerPage, $rowsPerPage) as $operation)
                    <tr>
                        <td>{{ $operation['page'] }}</td>
                        <td>{{ $operation['ligne'] }}</td>
                        <td>{{ $operation['n_enregistrement'] }}</td>
                        <td>{{ $operation['reference_document'] }}</td>
                        <td>{{ $operation['compte_general'] }}</td>
                        <td>{{ $operation['compte_analytique'] }}</td>
                        <td class="amount">
                            @if($operation['debit'] > 0)
                                {{ number_format($operation['debit'], 2, ',', ' ') }}
                                @php $totalDebit += $operation['debit']; @endphp
                            @endif
                        </td>
                        <td class="amount">
                            @if($operation['credit'] > 0)
                                {{ number_format($operation['credit'], 2, ',', ' ') }}
                                @php $totalCredit += $operation['credit']; @endphp
                            @endif
                        </td>
                        <td>{{ $operation['devise'] }}</td>
                        <td class="libelle">{{ $operation['libelle'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- FOOTER SECTION (only on last page) --}}
        @if($page == $totalPages)
            <div class="footer-section">
                <table class="bottom-layout-table">
                    <tr>
                        <td style="width: 55%;">
                            <div class="financial-summary">
                                <table>
                                    <tr><td>EN CAISSE LE 1<sup>er</sup> JOUR</td><td>{{ number_format($enCaisseDebut, 2, ',', ' ') }}</td></tr>
                                    <tr><td>ENCAISSEMENT REMB. PÉRIODE PRÉCÉDENTE</td><td>{{ number_format($encaissementRemb, 2, ',', ' ') }}</td></tr>
                                    <tr><td>MONTANT RÉGIE</td><td>{{ number_format($montantRegie, 2, ',', ' ') }}</td></tr>
                                    <tr><td>DÉPENSES DE LA PÉRIODE DU {{ $dateDebut }} AU {{ $dateFin }}</td><td>{{ number_format($depensesPeriode, 2, ',', ' ') }}</td></tr>
                                    <tr><td>EN CAISSE FIN DE PÉRIODE</td><td>{{ number_format($enCaisseFin, 2, ',', ' ') }}</td></tr>
                                    <tr><td>REMBOURSEMENT EFFECTUÉ PAR</td><td> DFC CBR N° {{ $numeroRemboursement }}</td></tr>
                                </table>
                            </div>
                        </td>
                        <td style="width: 40%;">
                            <div class="certification-signatures">
                                <p class="certification-text">
                                    JE CERTIFIE QUE L'ÉTAT CI-DESSUS EST VÉRITABLE ET CONFORME AUX DÉPENSES EFFECTUÉES POUR NAFTAL.
                                    LES PIÈCES JUSTIFICATIVES SONT JOINTES.
                                </p>
                                <div class="signature-grid">
                                    <div class="signature-block">
                                        <strong>LE RÉGISSEUR</strong><br>
                                        DATE: {{ $currentDate }}<br>
                                        <div class="signature-line"></div>
                                        SIGNATURE
                                    </div>
                                    <div class="signature-block">
                                        <strong>LE CHEF D'UNITÉ</strong><br>
                                        DATE: <br>
                                        <div class="signature-line"></div>
                                        SIGNATURE
                                    </div>
                                    <div class="signature-block">
                                        <strong>APPROBATION</strong><br>
                                        DATE: <br>
                                        <div class="signature-line"></div>
                                        SIGNATURE
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                @if(isset($qrCode))
                    <div class="qr-code-container">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code de vérification">
                        <div class="qr-code-label">Scan pour vérification</div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endfor

</body>
</html>
