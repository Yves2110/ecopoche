<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Paiement - EcoPoche</title>
<style>
  body { margin:0; padding:0; background:#F3F4F6; font-family:'Segoe UI',Arial,sans-serif; color:#1F2937; }
  .wrapper { max-width:580px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { padding:28px 32px; }
  .header.solde { background:#006c49; }
  .header.partiel { background:#002452; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .header p  { margin:4px 0 0; color:rgba(255,255,255,0.8); font-size:13px; }
  .body { padding:28px 32px; }
  .success-banner { background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
  .success-banner .title { font-size:14px; font-weight:700; color:#065f46; margin-bottom:4px; }
  .success-banner .sub { font-size:13px; color:#374151; }
  .info-banner { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px 20px; margin-bottom:20px; }
  .info-banner .title { font-size:14px; font-weight:700; color:#1e40af; margin-bottom:4px; }
  .info-banner .sub { font-size:13px; color:#374151; }
  table.details { width:100%; border-collapse:collapse; font-size:13px; }
  table.details td { padding:6px 0; border-bottom:1px solid #F3F4F6; }
  table.details td:first-child { color:#6B7280; }
  table.details td:last-child { font-weight:600; text-align:right; }
  .progress-bar { background:#E5E7EB; border-radius:999px; height:8px; margin:6px 0; overflow:hidden; }
  .progress-fill { height:8px; border-radius:999px; }
  .btn { display:inline-block; background:#002452; color:#fff; text-decoration:none; padding:10px 22px; border-radius:8px; font-size:13px; font-weight:600; margin-top:20px; }
  .footer { background:#F8FAFC; padding:16px 32px; text-align:center; font-size:11px; color:#9CA3AF; border-top:1px solid #E5E7EB; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header {{ $estSolde ? 'solde' : 'partiel' }}">
        @if($estSolde)
            <h1>🎉 Opération soldée !</h1>
        @else
            <h1>💳 Paiement enregistré</h1>
        @endif
        <p>{{ ucfirst($dette->type) }} - {{ $dette->partie }}</p>
    </div>

    <div class="body">
        <p>Bonjour {{ $user->full_name }},</p>

        @if($estSolde)
            <div class="success-banner">
                <div class="title">Félicitations, cette dette est entièrement soldée !</div>
                <div class="sub">
                    Montant total de {{ number_format((int) $dette->montant_initial, 0, ',', "\u{00A0}") }} FCFA
                    - dernier paiement de {{ number_format((int) $montant, 0, ',', "\u{00A0}") }} FCFA.
                </div>
            </div>
        @else
            <div class="info-banner">
                <div class="title">Paiement de {{ number_format((int) $montant, 0, ',', "\u{00A0}") }} FCFA enregistré</div>
                <div class="sub">
                    Il reste {{ number_format((int) $dette->montant_restant, 0, ',', "\u{00A0}") }} FCFA à {{ $dette->type === 'emprunt' ? 'rembourser' : 'recevoir' }}.
                </div>
            </div>
        @endif

        <h3 style="font-size:14px; margin:20px 0 10px;">Récapitulatif</h3>
        <table class="details">
            <tr><td>Type</td><td>{{ ucfirst($dette->type) }}</td></tr>
            <tr><td>Contrepartie</td><td>{{ $dette->partie }}</td></tr>
            <tr><td>Montant initial</td><td>{{ number_format((int) $dette->montant_initial, 0, ',', "\u{00A0}") }} FCFA</td></tr>
            <tr><td>Ce paiement</td><td style="color:#006c49; font-weight:700;">+ {{ number_format((int) $montant, 0, ',', "\u{00A0}") }} FCFA</td></tr>
            <tr><td>Total remboursé</td><td>{{ number_format((int) $dette->montant_rembourse, 0, ',', "\u{00A0}") }} FCFA</td></tr>
            <tr><td>Restant dû</td><td style="color:{{ $estSolde ? '#006c49' : '#DC2626' }};">{{ number_format((int) $dette->montant_restant, 0, ',', "\u{00A0}") }} FCFA</td></tr>
        </table>

        <div class="progress-bar">
            <div class="progress-fill" style="width:{{ $dette->pct_rembourse }}%; background:{{ $estSolde ? '#006c49' : '#3B82F6' }};"></div>
        </div>
        <p style="font-size:11px; color:#6B7280; margin:0;">{{ $dette->pct_rembourse }}% remboursé</p>

        @if($dette->date_echeance && !$estSolde)
            <p style="font-size:12px; color:#92400e; margin-top:12px; padding:8px 12px; background:#FEF3C7; border-radius:6px;">
                ⏰ Échéance : {{ $dette->date_echeance->translatedFormat('d F Y') }}
            </p>
        @endif

        @if(!$estSolde && count($conseils()) > 0)
        <div style="font-size:12px;font-weight:700;color:#374151;margin:20px 0 8px;">Conseils</div>
        <ul style="margin:0;padding-left:0;list-style:none;font-size:12px;color:#4B5563;">
            @foreach($conseils() as $conseil)
            <li style="padding:5px 0;border-bottom:1px solid #F3F4F6;">{{ $conseil }}</li>
            @endforeach
        </ul>
        @endif

        <a href="{{ url('/dettes') }}" class="btn">Voir mes dettes</a>
    </div>

    <div class="footer">
        EcoPoche · Cet email a été envoyé automatiquement.
    </div>
</div>
</body>
</html>
