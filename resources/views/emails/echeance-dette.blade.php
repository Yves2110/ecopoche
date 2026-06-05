<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Échéance - EcoPoche</title>
<style>
  body { margin:0; padding:0; background:#F3F4F6; font-family:'Segoe UI',Arial,sans-serif; color:#1F2937; }
  .wrapper { max-width:580px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { padding:28px 32px; }
  .header.proche { background:#F59E0B; }
  .header.depassee { background:#DC2626; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .header p  { margin:4px 0 0; color:rgba(255,255,255,0.85); font-size:13px; }
  .body { padding:28px 32px; }
  .alert-banner { border-radius:8px; padding:16px 20px; margin-bottom:20px; }
  .alert-banner.proche { background:#FEF3C7; border:1px solid #FDE68A; }
  .alert-banner.depassee { background:#fef2f2; border:1px solid #fecaca; }
  .alert-banner .title { font-size:14px; font-weight:700; margin-bottom:4px; }
  .alert-banner.proche .title { color:#92400E; }
  .alert-banner.depassee .title { color:#DC2626; }
  .alert-banner .sub { font-size:13px; color:#374151; }
  .kpi-row { display:flex; gap:12px; margin-bottom:20px; }
  .kpi { flex:1; background:#F8FAFC; border:1px solid #E5E7EB; border-radius:8px; padding:12px 14px; }
  .kpi .label { font-size:10px; font-weight:700; text-transform:uppercase; color:#6B7280; margin-bottom:4px; }
  .kpi .value { font-size:16px; font-weight:700; color:#002452; }
  .kpi.red .value { color:#DC2626; }
  .btn { display:inline-block; background:#002452; color:#fff; text-decoration:none; padding:10px 22px; border-radius:8px; font-size:13px; font-weight:600; margin-top:20px; }
  .footer { background:#F8FAFC; padding:16px 32px; text-align:center; font-size:11px; color:#9CA3AF; border-top:1px solid #E5E7EB; }
  .progress-bar { background:#E5E7EB; border-radius:999px; height:8px; margin:6px 0; overflow:hidden; }
  .progress-fill { height:8px; border-radius:999px; }
  table.details { width:100%; border-collapse:collapse; font-size:13px; }
  table.details td { padding:6px 0; border-bottom:1px solid #F3F4F6; }
  table.details td:first-child { color:#6B7280; }
  table.details td:last-child { font-weight:600; text-align:right; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header {{ $situation }}">
        @if($situation === 'depassee')
            <h1>⚠️ Échéance dépassée</h1>
            <p>{{ ucfirst($dette->type) }} de {{ $dette->partie }}</p>
        @else
            <h1>⏰ Échéance proche</h1>
            <p>{{ ucfirst($dette->type) }} de {{ $dette->partie }}</p>
        @endif
    </div>

    <div class="body">
        <p>Bonjour {{ $user->full_name }},</p>

        @if($situation === 'depassee')
            <div class="alert-banner depassee">
                <div class="title">L'échéance est dépassée de {{ abs($jours) }} jour{{ abs($jours) > 1 ? 's' : '' }}</div>
                <div class="sub">
                    @if($dette->type === 'emprunt')
                        Vous deviez rembourser cet emprunt au plus tard le {{ $dette->date_echeance->translatedFormat('d F Y') }}.
                    @else
                        {{ $dette->partie }} devait vous rembourser au plus tard le {{ $dette->date_echeance->translatedFormat('d F Y') }}.
                    @endif
                </div>
            </div>
        @else
            <div class="alert-banner proche">
                <div class="title">Échéance dans {{ $jours }} jour{{ $jours > 1 ? 's' : '' }}</div>
                <div class="sub">
                    Date limite : {{ $dette->date_echeance->translatedFormat('d F Y') }}.
                </div>
            </div>
        @endif

        <h3 style="font-size:14px; margin:20px 0 10px;">Détails</h3>
        <table class="details">
            <tr><td>Type</td><td>{{ ucfirst($dette->type) }}</td></tr>
            <tr><td>Contrepartie</td><td>{{ $dette->partie }}</td></tr>
            <tr><td>Montant initial</td><td>{{ number_format((int) $dette->montant_initial, 0, ',', "\u{00A0}") }} FCFA</td></tr>
            <tr><td>Déjà remboursé</td><td>{{ number_format((int) $dette->montant_rembourse, 0, ',', "\u{00A0}") }} FCFA</td></tr>
            <tr><td>Restant dû</td><td style="color:{{ $situation === 'depassee' ? '#DC2626' : '#F59E0B' }};">{{ number_format((int) $dette->montant_restant, 0, ',', "\u{00A0}") }} FCFA</td></tr>
        </table>

        <div class="progress-bar">
            <div class="progress-fill" style="width:{{ $dette->pct_rembourse }}%; background:{{ $situation === 'depassee' ? '#DC2626' : '#F59E0B' }};"></div>
        </div>
        <p style="font-size:11px; color:#6B7280; margin:0;">{{ $dette->pct_rembourse }}% remboursé</p>

        @if($dette->note)
            <p style="font-size:12px; color:#6B7280; margin-top:16px; padding:10px; background:#FEF3C7; border-radius:6px;">
                <strong>Note :</strong> {{ $dette->note }}
            </p>
        @endif

        <div class="section-title" style="font-size:12px;font-weight:700;color:#374151;margin:20px 0 8px;">Conseils</div>
        <ul>
            @foreach($conseils() as $conseil)
            <li>{{ $conseil }}</li>
            @endforeach
        </ul>

        <a href="{{ url('/dettes') }}" class="btn">Voir mes dettes</a>
    </div>

    <div class="footer">
        EcoPoche · Cet email a été envoyé automatiquement. Pour ne plus recevoir ces notifications, désactivez-les dans votre profil.
    </div>
</div>
</body>
</html>
