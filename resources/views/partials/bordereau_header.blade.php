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
        <td>
            CODE CARTE
            <strong> |1ère CARTE : 50 </strong>
            <strong> |2ème CARTE : 51 </strong>
        </td>
        <td>UNITE COMPT. <strong>652</strong></td>
        <td>PERIODE COMPT. <strong>{{ \Carbon\Carbon::parse($dra->date_creation)->format('m y') }}</strong></td>
        <td>CODE JOURNAL <strong>08</strong></td>
        <td>N° BORDEREAU <strong>{{ $dra->n_dra }}</strong></td>
    </tr>
</table>
