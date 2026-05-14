<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <title>Bilan {{ $moisLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1F2937; background: #fff; }

        .header { background: #002452; color: #fff; padding: 20px 28px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p  { font-size: 11px; color: rgba(255,255,255,0.65); margin-top: 3px; }
        .header .badge { display: inline-block; background: #006c49; color: #fff;
                         font-size: 9px; font-weight: 700; padding: 2px 8px;
                         border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; }

        .page { padding: 0 28px 28px; }

        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: 1px; color: #6B7280; margin: 18px 0 8px; border-bottom: 1px solid #E5E7EB; padding-bottom: 4px; }

        /* KPI row */
        .kpi-row { display: table; width: 100%; margin-bottom: 16px; }
        .kpi-cell { display: table-cell; width: 25%; padding: 10px 12px;
                    border: 1px solid #E5E7EB; border-radius: 8px; background: #F8FAFC; }
        .kpi-cell + .kpi-cell { margin-left: 8px; }
        .kpi-label { font-size: 9px; color: #6B7280; font-weight: 600; text-transform: uppercase; }
        .kpi-value { font-size: 14px; font-weight: 700; color: #002452; margin-top: 3px; }
        .kpi-value.green { color: #006c49; }
        .kpi-value.red   { color: #EF4444; }

        /* Sante banner */
        .sante-banner { padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid #006c49; background: #f0fdf4; }
        .sante-banner.rouge { border-color: #EF4444; background: #fef2f2; }
        .sante-banner.jaune { border-color: #F59E0B; background: #fffbeb; }
        .sante-title { font-size: 12px; font-weight: 700; color: #002452; }
        .sante-msg   { font-size: 10px; color: #6B7280; margin-top: 2px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #F8FAFC; font-size: 9px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: 0.5px; color: #6B7280; padding: 6px 10px; text-align: left;
                   border-bottom: 2px solid #E5E7EB; }
        thead th.r { text-align: right; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #F3F4F6; font-size: 10px; vertical-align: top; }
        tbody td.r { text-align: right; }
        tbody tr:nth-child(even) td { background: #F9FAFB; }
        tfoot td { padding: 7px 10px; font-size: 10px; font-weight: 700; background: #F8FAFC;
                   border-top: 2px solid #E5E7EB; }
        tfoot td.r { text-align: right; }

        .badge { display: inline-block; font-size: 8px; font-weight: 700; padding: 1px 6px;
                 border-radius: 10px; text-transform: uppercase; }
        .badge-red   { background: #fee2e2; color: #991b1b; }
        .badge-green { background: #d1fae5; color: #065f46; }

        /* Cat bar */
        .cat-row { margin-bottom: 6px; }
        .cat-label { font-size: 10px; font-weight: 600; color: #1F2937; margin-bottom: 2px; }
        .bar-track { width: 100%; height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
        .bar-fill  { height: 100%; border-radius: 4px; }

        .footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #E5E7EB;
                  font-size: 9px; color: #9CA3AF; text-align: center; }
        .two-col { display: table; width: 100%; }
        .col-left  { display: table-cell; width: 52%; vertical-align: top; padding-right: 14px; }
        .col-right { display: table-cell; width: 48%; vertical-align: top; }
    </style>
</head>
<body>

<div class="header">
    <h1>EcoPoche — Bilan Mensuel</h1>
    <p>{{ $user->name }} &nbsp;·&nbsp; {{ $user->email }}</p>
    <span class="badge">{{ $moisLabel }}</span>
</div>

<div class="page">

    {{-- Santé budgétaire --}}
    @php
        $santeClass = match($sante) { 'critique'=>'rouge', 'attention'=>'jaune', default=>'' };
        $santeMsg   = match($sante) {
            'sain'      => 'Budget maîtrisé — dépenses en bonne trajectoire.',
            'attention' => 'Attention — plus de 70% des revenus dépensés.',
            'critique'  => 'Budget dépassé — dépenses excèdent les revenus.',
            default     => 'Aucun revenu configuré pour ce mois.',
        };
    @endphp
    <div class="sante-banner {{ $santeClass }}">
        <div class="sante-title">{{ match($sante) { 'sain'=>'Budget maîtrisé', 'attention'=>'Attention au budget', 'critique'=>'Budget dépassé !', default=>'Budget non configuré' } }}</div>
        <div class="sante-msg">{{ $santeMsg }}</div>
    </div>

    {{-- KPIs --}}
    <p class="section-title">Synthèse financière</p>
    <table style="margin-bottom:14px;">
        <tr>
            <td style="width:25%;padding:8px 10px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:6px;">
                <div class="kpi-label">Revenu fixe</div>
                <div class="kpi-value">{{ number_format($salaire, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:4%;"></td>
            <td style="width:25%;padding:8px 10px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:6px;">
                <div class="kpi-label">Bonus dépensable</div>
                <div class="kpi-value green">{{ number_format((int)$totalDepensable, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:4%;"></td>
            <td style="width:25%;padding:8px 10px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:6px;">
                <div class="kpi-label">Total dépensé</div>
                <div class="kpi-value red">{{ number_format((int)$totalDepenses, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:4%;"></td>
            <td style="width:25%;padding:8px 10px;border:1px solid {{ $solde >= 0 ? '#006c49' : '#EF4444' }};background:{{ $solde >= 0 ? '#f0fdf4' : '#fef2f2' }};border-radius:6px;">
                <div class="kpi-label">Solde disponible</div>
                <div class="kpi-value {{ $solde >= 0 ? 'green' : 'red' }}">{{ number_format((int)$solde, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
    </table>

    <div class="two-col">
        <div class="col-left">

            {{-- Dépenses --}}
            <p class="section-title">Dépenses ({{ $depenses->count() }} opération(s))</p>
            @if($depenses->isEmpty())
                <p style="color:#9CA3AF;font-size:10px;font-style:italic;margin-bottom:12px;">Aucune dépense ce mois-ci.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th class="r">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($depenses as $dep)
                    <tr>
                        <td>{{ $dep->date->format('d/m') }}</td>
                        <td>
                            {{ $dep->note ?: ($dep->categorie?->nom ?? '—') }}
                            @if($dep->imprevue)<span class="badge badge-red">Imprévue</span>@endif
                        </td>
                        <td>{{ $dep->categorie?->nom ?? 'Autres' }}</td>
                        <td class="r" style="color:#EF4444;font-weight:700;">{{ number_format((int)$dep->montant, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total dépenses</td>
                        <td class="r" style="color:#EF4444;">{{ number_format((int)$totalDepenses, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
            @endif

            {{-- Revenus variables --}}
            @if($revenus->where('quota_applique', true)->count())
            <p class="section-title">Revenus variables (quota 30%)</p>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="r">Brut</th>
                        <th class="r">Réserve (70%)</th>
                        <th class="r">Dispo (30%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenus->where('quota_applique', true) as $rev)
                    <tr>
                        <td style="text-transform:capitalize;">{{ $rev->type }}</td>
                        <td>{{ $rev->description ?? '—' }}</td>
                        <td class="r">{{ number_format((int)$rev->montant_brut, 0, ',', ' ') }}</td>
                        <td class="r" style="color:#006c49;">{{ number_format((int)$rev->montant_dispo, 0, ',', ' ') }}</td>
                        <td class="r" style="color:#002452;">{{ number_format((int)$rev->montant_quota, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

        </div>
        <div class="col-right">

            {{-- Répartition par catégorie --}}
            <p class="section-title">Répartition par catégorie</p>
            @if($parCategorie->isEmpty())
                <p style="color:#9CA3AF;font-size:10px;font-style:italic;margin-bottom:12px;">Aucune dépense catégorisée.</p>
            @else
            @foreach($parCategorie as $cat)
            @php $pct = $totalDepenses > 0 ? round($cat['total'] / $totalDepenses * 100) : 0; @endphp
            <div class="cat-row">
                <div style="display:table;width:100%;margin-bottom:3px;">
                    <span style="display:table-cell;font-size:10px;font-weight:600;color:#1F2937;">{{ $cat['nom'] }}</span>
                    <span style="display:table-cell;text-align:right;font-size:10px;color:#6B7280;">{{ number_format($cat['total'], 0, ',', ' ') }} FCFA &nbsp; <strong>{{ $pct }}%</strong></span>
                </div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $pct }}%;background:{{ $cat['couleur'] }};"></div>
                </div>
            </div>
            @endforeach
            @endif

            {{-- Épargne --}}
            <p class="section-title">Épargne du mois</p>
            @if($epargne)
            <table>
                <tbody>
                    <tr>
                        <td style="font-weight:600;">Objectif</td>
                        <td class="r">{{ number_format((int)$epargne->objectif, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;color:#006c49;">Réel épargné</td>
                        <td class="r" style="color:#006c49;font-weight:700;">{{ number_format((int)$epargne->reel, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td style="font-weight:600;{{ $epargne->deficit > 0 ? 'color:#EF4444;' : '' }}">Déficit</td>
                        <td class="r" style="{{ $epargne->deficit > 0 ? 'color:#EF4444;font-weight:700;' : 'color:#006c49;' }}">
                            {{ $epargne->deficit > 0 ? '− '.number_format((int)$epargne->deficit, 0, ',', ' ').' FCFA' : 'Aucun' }}
                        </td>
                    </tr>
                    @if($epargne->analyse)
                    <tr>
                        <td colspan="2" style="color:#6B7280;font-style:italic;font-size:9px;">{{ $epargne->analyse }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @else
                <p style="color:#9CA3AF;font-size:10px;font-style:italic;">Aucune épargne saisie ce mois.</p>
            @endif

        </div>
    </div>

</div>

<div class="footer">
    Généré par EcoPoche le {{ now()->translatedFormat('d F Y à H:i') }}
</div>

</body>
</html>
