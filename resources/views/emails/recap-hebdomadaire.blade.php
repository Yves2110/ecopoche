<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Récapitulatif hebdomadaire — EcoPoche</title>
<style>
  body { margin:0; padding:0; background:#F3F4F6; font-family:'Segoe UI',Arial,sans-serif; color:#1F2937; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#002452; padding:28px 32px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .header p  { margin:4px 0 0; color:#93c5fd; font-size:13px; }
  .body { padding:28px 32px; }
  .kpi-row { display:flex; gap:12px; margin-bottom:24px; }
  .kpi { flex:1; background:#F8FAFC; border:1px solid #E5E7EB; border-radius:8px; padding:14px 16px; }
  .kpi .label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6B7280; margin-bottom:4px; }
  .kpi .value { font-size:18px; font-weight:700; color:#002452; }
  .kpi.green  { border-color:#006c49; background:#f0fdf4; }
  .kpi.green .value { color:#006c49; }
  .kpi.red    { border-color:#DC2626; background:#fef2f2; }
  .kpi.red .value { color:#DC2626; }
  .kpi.orange { border-color:#D97706; background:#fffbeb; }
  .kpi.orange .value { color:#D97706; }
  .progress-bar { background:#E5E7EB; border-radius:999px; height:8px; margin:8px 0 4px; overflow:hidden; }
  .progress-fill { height:8px; border-radius:999px; }
  .section-title { font-size:13px; font-weight:700; color:#1F2937; margin:20px 0 10px; padding-bottom:6px; border-bottom:1px solid #F3F4F6; }
  table { width:100%; border-collapse:collapse; font-size:12px; }
  th { text-align:left; padding:6px 10px; color:#6B7280; font-size:10px; text-transform:uppercase; letter-spacing:.06em; background:#F8FAFC; }
  td { padding:8px 10px; border-bottom:1px solid #F3F4F6; }
  .btn { display:inline-block; background:#002452; color:#fff; text-decoration:none; padding:10px 22px; border-radius:8px; font-size:13px; font-weight:600; margin-top:20px; }
  .footer { background:#F8FAFC; padding:16px 32px; text-align:center; font-size:11px; color:#9CA3AF; border-top:1px solid #E5E7EB; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>EcoPoche</h1>
    <p>Récapitulatif budgétaire — {{ now()->translatedFormat('d F Y') }}</p>
  </div>

  <div class="body">
    <p style="font-size:14px; margin:0 0 20px">Bonjour <strong>{{ $user->name }}</strong>, voici l'état de votre budget pour le mois de <strong>{{ \Carbon\Carbon::createFromDate($budget->annee, $budget->mois, 1)->translatedFormat('F Y') }}</strong>.</p>

    @php
      $barColor = $ratio >= 1 ? '#DC2626' : ($ratio >= 0.70 ? '#D97706' : '#006c49');
      $barWidth  = min(100, round($ratio * 100));
      $kpiClass  = $solde < 0 ? 'red' : ($ratio >= 0.70 ? 'orange' : 'green');
    @endphp

    <div class="kpi-row">
      <div class="kpi">
        <div class="label">Budget total</div>
        <div class="value">{{ number_format((int)$budgetTotal, 0, ',', "\u{00A0}") }} FCFA</div>
      </div>
      <div class="kpi">
        <div class="label">Dépensé</div>
        <div class="value">{{ number_format((int)$totalDepenses, 0, ',', "\u{00A0}") }} FCFA</div>
      </div>
      <div class="kpi {{ $kpiClass }}">
        <div class="label">Solde restant</div>
        <div class="value">{{ number_format((int)$solde, 0, ',', "\u{00A0}") }} FCFA</div>
      </div>
    </div>

    <div class="label" style="font-size:11px;color:#6B7280;margin-bottom:4px">Consommation du budget — {{ $barWidth }}%</div>
    <div class="progress-bar">
      <div class="progress-fill" style="width:{{ $barWidth }}%; background:{{ $barColor }}"></div>
    </div>
    <p style="font-size:11px;color:#6B7280;margin:4px 0 20px">
      {{ $barWidth }}% consommé
      @if($solde < 0)
        — Budget dépassé de {{ number_format((int)abs($solde), 0, ',', "\u{00A0}") }} FCFA
      @endif
    </p>

    @if(count($topCategories) > 0)
    <div class="section-title">Top dépenses par catégorie</div>
    <table>
      <tr>
        <th>Catégorie</th>
        <th style="text-align:right">Montant</th>
        <th style="text-align:right">Opérations</th>
      </tr>
      @foreach($topCategories as $cat)
      <tr>
        <td>{{ $cat['nom'] }}</td>
        <td style="text-align:right; font-weight:600">{{ number_format((int)$cat['total'], 0, ',', "\u{00A0}") }} FCFA</td>
        <td style="text-align:right; color:#6B7280">{{ $cat['nb'] }}</td>
      </tr>
      @endforeach
    </table>
    @endif

    <a href="{{ url('/dashboard') }}" class="btn">Voir mon tableau de bord</a>
  </div>

  <div class="footer">
    EcoPoche &mdash; Gestion budgétaire personnelle &bull; Vous recevez ce mail car vous êtes inscrit sur EcoPoche.<br>
    Pour se désinscrire, rendez-vous dans vos <a href="{{ url('/parametres') }}" style="color:#6B7280">paramètres</a>.
  </div>
</div>
</body>
</html>
