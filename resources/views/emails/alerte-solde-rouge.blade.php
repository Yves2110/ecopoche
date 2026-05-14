<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Alerte budget — EcoPoche</title>
<style>
  body { margin:0; padding:0; background:#F3F4F6; font-family:'Segoe UI',Arial,sans-serif; color:#1F2937; }
  .wrapper { max-width:580px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#DC2626; padding:28px 32px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .header p  { margin:4px 0 0; color:#fecaca; font-size:13px; }
  .alert-banner { background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
  .alert-banner .title { font-size:14px; font-weight:700; color:#DC2626; margin-bottom:4px; }
  .alert-banner .sub   { font-size:13px; color:#374151; }
  .body { padding:28px 32px; }
  .kpi-row { display:flex; gap:12px; margin-bottom:20px; }
  .kpi { flex:1; background:#F8FAFC; border:1px solid #E5E7EB; border-radius:8px; padding:12px 14px; }
  .kpi .label { font-size:10px; font-weight:700; text-transform:uppercase; color:#6B7280; margin-bottom:4px; }
  .kpi .value { font-size:16px; font-weight:700; color:#002452; }
  .kpi.red .value { color:#DC2626; }
  .section-title { font-size:12px; font-weight:700; color:#374151; margin:16px 0 8px; }
  ul { margin:0; padding-left:0; list-style:none; }
  ul li { font-size:12px; color:#4B5563; padding:5px 0; border-bottom:1px solid #F3F4F6; display:flex; align-items:flex-start; gap:8px; }
  ul li:last-child { border-bottom:none; }
  ul li::before { content:'—'; color:#9CA3AF; flex-shrink:0; }
  .btn { display:inline-block; background:#002452; color:#fff; text-decoration:none; padding:10px 22px; border-radius:8px; font-size:13px; font-weight:600; margin-top:20px; }
  .footer { background:#F8FAFC; padding:16px 32px; text-align:center; font-size:11px; color:#9CA3AF; border-top:1px solid #E5E7EB; }
  .progress-bar { background:#E5E7EB; border-radius:999px; height:8px; margin:4px 0; overflow:hidden; }
  .progress-fill { height:8px; border-radius:999px; background:#DC2626; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>EcoPoche — Alerte budget</h1>
    <p>{{ now()->translatedFormat('d F Y à H:i') }}</p>
  </div>

  <div class="body">
    <p style="font-size:14px;margin:0 0 16px">Bonjour <strong>{{ $user->name }}</strong>,</p>

    @php
      $barWidth = min(100, round($ratio * 100));
      $moisLabel = \Carbon\Carbon::createFromDate($budget->annee, $budget->mois, 1)->translatedFormat('F Y');
    @endphp

    <div class="alert-banner">
      <div class="title">
        @if($solde < 0)
          Budget dépassé — action requise
        @else
          Budget en zone critique ({{ $barWidth }}%)
        @endif
      </div>
      <div class="sub">
        Votre budget de <strong>{{ $moisLabel }}</strong> atteint <strong>{{ $barWidth }}%</strong> de consommation.
        @if($solde < 0)
          Solde négatif : <strong>{{ number_format((int)abs($solde), 0, ',', "\u{00A0}") }} FCFA</strong> de dépassement.
        @else
          Solde restant : <strong>{{ number_format((int)$solde, 0, ',', "\u{00A0}") }} FCFA</strong>.
        @endif
      </div>
    </div>

    <div class="kpi-row">
      <div class="kpi">
        <div class="label">Budget total</div>
        <div class="value">{{ number_format((int)$budgetTotal, 0, ',', "\u{00A0}") }} FCFA</div>
      </div>
      <div class="kpi red">
        <div class="label">Solde restant</div>
        <div class="value">{{ number_format((int)$solde, 0, ',', "\u{00A0}") }} FCFA</div>
      </div>
    </div>

    <div style="font-size:11px;color:#6B7280;margin-bottom:4px">Consommation : {{ $barWidth }}%</div>
    <div class="progress-bar"><div class="progress-fill" style="width:{{ $barWidth }}%"></div></div>

    <div class="section-title">Actions recommandées</div>
    <ul>
      <li>Geler toutes les dépenses non essentielles jusqu'à la fin du mois.</li>
      <li>Envisager de débloquer une partie de votre réserve si disponible.</li>
      <li>Réduire les sorties, loisirs et achats imprévus.</li>
      <li>Consulter votre tableau de bord pour identifier les catégories en dépassement.</li>
    </ul>

    <a href="{{ url('/depenses') }}" class="btn">Voir mes dépenses</a>
  </div>

  <div class="footer">
    EcoPoche &mdash; Gestion budgétaire personnelle &bull; Vous recevez ce mail car votre solde est passé en zone critique.<br>
    Pour gérer vos préférences, rendez-vous dans vos <a href="{{ url('/parametres') }}" style="color:#6B7280">paramètres</a>.
  </div>
</div>
</body>
</html>
