<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <title>Rapport comparatif</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1F2937; background: #fff; }

        .header { background: #002452; color: #fff; padding: 18px 28px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header p  { font-size: 10px; color: #9ab0cc; margin-top: 3px; }
        .badge-h { display:inline-block; background:#006c49; color:#fff; font-size:9px; font-weight:700;
                   padding:2px 8px; border-radius:20px; text-transform:uppercase; margin-top:5px; }

        .page { padding: 0 28px 28px; }
        .section-title { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px;
                         color:#6B7280; margin:16px 0 8px; border-bottom:1px solid #E5E7EB; padding-bottom:4px; }

        /* KPI row */
        .kpi-row { display:table; width:100%; margin-bottom:14px; border-collapse:separate; border-spacing:6px; }
        .kpi-cell { display:table-cell; width:25%; padding:9px 10px;
                    border:1px solid #E5E7EB; border-radius:6px; background:#F8FAFC; }
        .kpi-label { font-size:8px; color:#6B7280; font-weight:600; text-transform:uppercase; }
        .kpi-value { font-size:13px; font-weight:700; color:#002452; margin-top:2px; }
        .kpi-value.green { color:#006c49; }
        .kpi-value.red   { color:#EF4444; }
        .kpi-sub  { font-size:8px; color:#9CA3AF; margin-top:1px; }

        /* Table */
        table { width:100%; border-collapse:collapse; margin-bottom:14px; }
        thead th { background:#F8FAFC; font-size:8px; font-weight:700; text-transform:uppercase;
                   letter-spacing:0.5px; color:#6B7280; padding:5px 8px; text-align:left;
                   border-bottom:2px solid #E5E7EB; }
        thead th.r { text-align:right; }
        tbody td { padding:5px 8px; border-bottom:1px solid #F3F4F6; font-size:9px; }
        tbody td.r { text-align:right; }
        tbody tr:nth-child(even) td { background:#F9FAFB; }
        tfoot td { padding:6px 8px; font-size:9px; font-weight:700; background:#F8FAFC; border-top:2px solid #E5E7EB; }
        tfoot td.r { text-align:right; }

        .badge { display:inline-block; font-size:7px; font-weight:700; padding:1px 5px; border-radius:8px; text-transform:uppercase; }
        .badge-red   { background:#fee2e2; color:#991b1b; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-yellow{ background:#fef3c7; color:#92400e; }
        .badge-gray  { background:#F3F4F6; color:#6B7280; }

        .chart-wrap { background:#F8FAFC; border:1px solid #E5E7EB; border-radius:8px; padding:14px 16px; margin-bottom:14px; }
        .chart-title { font-size:10px; font-weight:700; color:#1F2937; margin-bottom:10px; }
        .chart-legend { font-size:8px; color:#6B7280; margin-bottom:8px; }
        .chart-legend span { margin-right:12px; }

        /* Cat bars */
        .cat-row { margin-bottom:5px; }
        .bar-track { width:100%; height:7px; background:#E5E7EB; border-radius:3px; overflow:hidden; }
        .bar-fill  { height:100%; border-radius:3px; }

        .two-col { display:table; width:100%; }
        .col60 { display:table-cell; width:62%; vertical-align:top; padding-right:14px; }
        .col40 { display:table-cell; width:38%; vertical-align:top; }

        .footer { margin-top:24px; padding-top:8px; border-top:1px solid #E5E7EB;
                  font-size:8px; color:#9CA3AF; text-align:center; }
    </style>
</head>
<body>

<div class="header">
    <h1>EcoPoche — Rapport Comparatif</h1>
    <p>{{ $user->name }} &nbsp;·&nbsp; {{ $user->email }}</p>
    <span class="badge-h">{{ $periodeLabel }}</span>
</div>

<div class="page">

    {{-- KPIs globaux --}}
    <p class="section-title">Synthèse sur la période</p>
    <table style="margin-bottom:12px;">
        <tr>
            <td style="width:23%;padding:8px 10px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:5px;">
                <div class="kpi-label">Total Revenus</div>
                <div class="kpi-value">{{ number_format($totalRevenu, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:3%;"></td>
            <td style="width:23%;padding:8px 10px;border:1px solid #EF4444;background:#fef2f2;border-radius:5px;">
                <div class="kpi-label">Total Dépenses</div>
                <div class="kpi-value red">{{ number_format($totalDepenses, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:3%;"></td>
            <td style="width:23%;padding:8px 10px;border:1px solid #006c49;background:#f0fdf4;border-radius:5px;">
                <div class="kpi-label">Total Épargné</div>
                <div class="kpi-value green">{{ number_format($totalEpargne, 0, ',', ' ') }} FCFA</div>
            </td>
            <td style="width:3%;"></td>
            <td style="width:23%;padding:8px 10px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:5px;">
                <div class="kpi-label">Taux d'épargne</div>
                <div class="kpi-value {{ $tauxEpargne >= 15 ? 'green' : ($tauxEpargne >= 5 ? '' : 'red') }}">{{ $tauxEpargne }}%</div>
                <div class="kpi-sub">{{ $tauxEpargne >= 15 ? 'Excellent' : ($tauxEpargne >= 5 ? 'Correct' : 'Faible') }}</div>
            </td>
        </tr>
    </table>

    {{-- ===== LIGNE 1 : Graphique comparatif (gauche) + Top catégories (droite) ===== --}}
    @php
        $maxVal = max(
            collect($historique)->max('revenu'),
            collect($historique)->max('depenses'),
            1
        );
        $chartW  = 380;
        $chartH  = 150;
        $padL    = 38; $padB = 22; $padR = 8; $padT = 8;
        $innerW  = $chartW - $padL - $padR;
        $innerH  = $chartH - $padB - $padT;
        $n       = count($historique);
        $groupW  = $innerW / max($n, 1);
        $barW    = max(4, min(14, $groupW / 4));
        $gap     = $barW * 0.5;
        $svgYfn  = fn($val, $max, $iH, $pT) => $max == 0 ? $pT + $iH : $pT + $iH - ($val / $max) * $iH;
        $svgHfn  = fn($val, $max, $iH)      => $max == 0 ? 0 : max(1, ($val / $max) * $iH);
    @endphp

    <div style="display:table;width:100%;margin-bottom:14px;">

        {{-- Colonne gauche : graphique comparatif --}}
        <div style="display:table-cell;width:60%;vertical-align:top;padding-right:12px;">
            <div class="chart-wrap">
                <div class="chart-title">Comparatif {{ $periodeLabel }} — Revenus / Dépenses / Épargne</div>
                <div class="chart-legend">
                    <span>&#9632; <span style="color:#002452">Revenus</span></span>
                    <span>&#9632; <span style="color:#EF4444">Dépenses</span></span>
                    <span>&#9632; <span style="color:#006c49">Épargne</span></span>
                </div>
                <svg width="{{ $chartW }}" height="{{ $chartH }}" xmlns="http://www.w3.org/2000/svg">
                    @for($gi = 0; $gi <= 4; $gi++)
                    @php $gy = $padT + ($innerH / 4) * $gi; $gval = round($maxVal * (4 - $gi) / 4 / 1000); @endphp
                    <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $chartW - $padR }}" y2="{{ $gy }}"
                          stroke="#E5E7EB" stroke-width="0.5"/>
                    <text x="{{ $padL - 3 }}" y="{{ $gy + 3 }}" font-size="7" fill="#9CA3AF" text-anchor="end">{{ $gval }}k</text>
                    @endfor
                    <line x1="{{ $padL }}" y1="{{ $padT + $innerH }}" x2="{{ $chartW - $padR }}" y2="{{ $padT + $innerH }}"
                          stroke="#E5E7EB" stroke-width="1"/>
                    @foreach($historique as $idx => $h)
                    @php
                        $cx = $padL + $groupW * $idx + $groupW / 2;
                        $bx1 = $cx - $barW * 1.5 - $gap;
                        $bx2 = $cx - $barW * 0.5;
                        $bx3 = $cx + $barW * 0.5 + $gap;
                        $hRev = $svgHfn($h['revenu'],   $maxVal, $innerH);
                        $hDep = $svgHfn($h['depenses'], $maxVal, $innerH);
                        $hEp  = $svgHfn($h['epargne'],  $maxVal, $innerH);
                        $yRev = $svgYfn($h['revenu'],   $maxVal, $innerH, $padT);
                        $yDep = $svgYfn($h['depenses'], $maxVal, $innerH, $padT);
                        $yEp  = $svgYfn($h['epargne'],  $maxVal, $innerH, $padT);
                        $lp   = explode(' ', $h['label']);
                        $sl   = strtoupper(substr($lp[0] ?? '', 0, 3)) . ' ' . ($lp[1] ?? '');
                    @endphp
                    <rect x="{{ $bx1 }}" y="{{ $yRev }}" width="{{ $barW }}" height="{{ $hRev }}" fill="#002452" fill-opacity="0.3" rx="1"/>
                    <rect x="{{ $bx2 }}" y="{{ $yDep }}" width="{{ $barW }}" height="{{ $hDep }}" fill="#EF4444" fill-opacity="0.85" rx="1"/>
                    <rect x="{{ $bx3 }}" y="{{ $yEp  }}" width="{{ $barW }}" height="{{ $hEp  }}" fill="#006c49" fill-opacity="0.9" rx="1"/>
                    <text x="{{ $cx }}" y="{{ $padT + $innerH + 15 }}" font-size="6.5" fill="#9CA3AF" text-anchor="middle">{{ $sl }}</text>
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Colonne droite : Top catégories --}}
        <div style="display:table-cell;width:40%;vertical-align:top;">
            <p class="section-title">Top dépenses / catégorie</p>
            @if($topCategories->isEmpty())
                <p style="color:#9CA3AF;font-size:9px;font-style:italic;margin-top:8px;">Aucune dépense.</p>
            @else
            @foreach($topCategories as $i => $cat)
            @php $pctCat = $topCategories->first()['total'] > 0 ? round($cat['total'] / $topCategories->first()['total'] * 100) : 0; @endphp
            <div class="cat-row">
                <div style="display:table;width:100%;margin-bottom:2px;">
                    <span style="display:table-cell;font-size:9px;font-weight:600;">
                        <span style="color:{{ $cat['couleur'] }}">●</span> {{ $cat['nom'] }}
                    </span>
                    <span style="display:table-cell;text-align:right;font-size:9px;color:#6B7280;">
                        {{ number_format($cat['total'], 0, ',', ' ') }} &nbsp;<strong>{{ $pctCat }}%</strong>
                    </span>
                </div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $pctCat }}%;background:{{ $cat['couleur'] }};"></div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

    </div>

    {{-- ===== LIGNE 2 : Tableau mensuel (gauche) + Évolution épargne (droite) ===== --}}
    <div style="display:table;width:100%;">

        <div style="display:table-cell;width:65%;vertical-align:top;padding-right:12px;">
            <p class="section-title">Détail par mois</p>
            <table>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th class="r">Revenus</th>
                        <th class="r">Dépenses</th>
                        <th class="r">Solde</th>
                        <th class="r">Épargne</th>
                        <th class="r">Santé</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_reverse($historique) as $h)
                    @php
                        $taux = $h['revenu'] > 0 ? round($h['depenses'] / $h['revenu'] * 100) : 0;
                        $sante = match(true) {
                            $h['revenu'] == 0 => ['l'=>'—',        'cls'=>'badge-gray'],
                            $h['solde'] < 0   => ['l'=>'Dépassé',  'cls'=>'badge-red'],
                            $taux >= 70        => ['l'=>'Attention','cls'=>'badge-yellow'],
                            default            => ['l'=>'Sain',     'cls'=>'badge-green'],
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:600;">
                            {{ $h['label'] }}
                            @if($h['actif'])<span class="badge badge-green">Actuel</span>@endif
                        </td>
                        <td class="r">{{ $h['revenu'] > 0 ? number_format($h['revenu'], 0, ',', ' ') : '—' }}</td>
                        <td class="r" style="{{ $h['depenses'] > 0 ? 'color:#EF4444;' : 'color:#9CA3AF;' }}">
                            {{ $h['depenses'] > 0 ? number_format($h['depenses'], 0, ',', ' ') : '—' }}
                        </td>
                        <td class="r" style="{{ $h['solde'] >= 0 ? 'color:#006c49;font-weight:700;' : 'color:#EF4444;font-weight:700;' }}">
                            {{ $h['revenu'] > 0 ? (($h['solde'] >= 0 ? '+' : '') . number_format($h['solde'], 0, ',', ' ')) : '—' }}
                        </td>
                        <td class="r" style="color:#006c49;font-weight:600;">
                            {{ $h['epargne'] > 0 ? number_format($h['epargne'], 0, ',', ' ') : '—' }}
                        </td>
                        <td class="r"><span class="badge {{ $sante['cls'] }}">{{ $sante['l'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTAL</td>
                        <td class="r">{{ number_format($totalRevenu, 0, ',', ' ') }}</td>
                        <td class="r" style="color:#EF4444;">{{ number_format($totalDepenses, 0, ',', ' ') }}</td>
                        <td class="r" style="{{ ($totalRevenu-$totalDepenses)>=0 ? 'color:#006c49;' : 'color:#EF4444;' }}">
                            {{ number_format($totalRevenu - $totalDepenses, 0, ',', ' ') }}
                        </td>
                        <td class="r" style="color:#006c49;">{{ number_format($totalEpargne, 0, ',', ' ') }}</td>
                        <td class="r" style="color:#002452;">{{ $tauxEpargne }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Évolution épargne (courbe) --}}
        <div style="display:table-cell;width:35%;vertical-align:top;">
            @php
                $epargnes = array_column($historique, 'epargne');
                $maxEp    = max(max($epargnes), 1);
                $svgW2=210; $svgH2=90;
                $pL2=22; $pB2=18; $pR2=6; $pT2=8;
                $iW2=$svgW2-$pL2-$pR2; $iH2=$svgH2-$pB2-$pT2;
                $nEp=count($epargnes);
                $pts=[];
                foreach($epargnes as $ei=>$ev){
                    $px=$pL2+($nEp>1?$iW2*$ei/($nEp-1):$iW2/2);
                    $py=$pT2+$iH2-($maxEp>0?($ev/$maxEp)*$iH2:0);
                    $pts[]="$px,$py";
                }
                $polyline=implode(' ',$pts);
            @endphp
            <p class="section-title">Évolution de l'épargne</p>
            <div class="chart-wrap" style="padding:10px 12px;">
                <svg width="{{ $svgW2 }}" height="{{ $svgH2 }}" xmlns="http://www.w3.org/2000/svg">
                    @for($gi=0;$gi<=3;$gi++)
                    @php $gy2=$pT2+($iH2/3)*$gi; $gv2=round($maxEp*(3-$gi)/3/1000,1); @endphp
                    <line x1="{{ $pL2 }}" y1="{{ $gy2 }}" x2="{{ $svgW2-$pR2 }}" y2="{{ $gy2 }}" stroke="#E5E7EB" stroke-width="0.5"/>
                    <text x="{{ $pL2-3 }}" y="{{ $gy2+3 }}" font-size="6" fill="#9CA3AF" text-anchor="end">{{ $gv2 }}k</text>
                    @endfor
                    @if(count($pts)>=2)
                    @php
                        $lx=explode(',',$pts[count($pts)-1])[0];
                        $fx=explode(',',$pts[0])[0];
                        $bl=$pT2+$iH2;
                        $area=$polyline." $lx,$bl $fx,$bl";
                    @endphp
                    <polygon points="{{ $area }}" fill="#006c49" fill-opacity="0.08"/>
                    <polyline points="{{ $polyline }}" fill="none" stroke="#006c49" stroke-width="1.5" stroke-linejoin="round"/>
                    @foreach($epargnes as $ei=>$ev)
                    @php $dpx=$pL2+($nEp>1?$iW2*$ei/($nEp-1):$iW2/2); $dpy=$pT2+$iH2-($maxEp>0?($ev/$maxEp)*$iH2:0); @endphp
                    @if($ev>0)<circle cx="{{ $dpx }}" cy="{{ $dpy }}" r="2" fill="#006c49"/>@endif
                    @endforeach
                    @endif
                    @foreach($historique as $ei=>$h)
                    @php $lx2=$pL2+($nEp>1?$iW2*$ei/($nEp-1):$iW2/2); $lp=explode(' ',$h['label']); @endphp
                    <text x="{{ $lx2 }}" y="{{ $svgH2-3 }}" font-size="6" fill="#9CA3AF" text-anchor="middle">{{ strtoupper(substr($lp[0]??'',0,1)) }}</text>
                    @endforeach
                </svg>
            </div>
        </div>

    </div>

</div>

<div class="footer">
    Rapport généré par EcoPoche le {{ now()->translatedFormat('d F Y à H:i') }}
    &nbsp;·&nbsp; Période : {{ $periodeLabel }}
</div>

</body>
</html>
