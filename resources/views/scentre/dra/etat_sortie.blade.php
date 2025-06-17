<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>État de Sortie - DRA {{ $dra->n_dra }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; text-align: right; }
        .total-row { font-weight: bold; }
    </style>
</head>
<body>
<div class="header">
    <h2>BRANCHE CARBURANTS</h2>
    <h3>Direction Marine Code:1A80</h3>
    <h1>ÉTAT DE SORTIE</h1>
    <p>Période du {{ $dateDebut }} au {{ $dateFin }}</p>
    <p>Exercice: 2025</p>
</div>

<table>
    <thead>
    <tr>
        <th>N° DRA</th>
        <th>Date création</th>
        <th>Libellé</th>
        <th>Montant</th>
        <th>TVA</th>
        <th>Droit Timbre</th>
        <th>Nombre Pièce</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
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
    <tr class="total-row">
        <td colspan="3">TOTAL PÉRIODE</td>
        <td class="text-right">{{ $totalMontant }}</td>
        <td class="text-right">{{ $totalTVA }}</td>
        <td class="text-right">{{ $totalDroitTimbre }}</td>
        <td class="text-center">{{ $items->sum('nombre_piece') }}</td>
        <td class="text-right">{{ $totalGeneral }}</td>
    </tr>
    </tbody>
</table>

<div class="footer">
    <p>SOLDE PÉRIODE / REPORT PÉRIODE: {{ $totalGeneral }}</p>
    <p>NB: LE SOLDE FIN DE PÉRIODE DOIT ÊTRE DÉBITEUR OU NUL EN AUCUN CAS IL PRÉSENTE UN SOLDE NÉGATIF</p>
    <p>S'IL S'AGIT DE PLUSIEUR PAGES IL Y'A LIEU D'INDIQUER LE REPORT DES PAGES</p>
</div>
</body>
</html>
