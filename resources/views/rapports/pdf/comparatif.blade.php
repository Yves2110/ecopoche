<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <title>Rapport comparatif</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:DejaVu Sans,sans-serif; font-size:10px; color:#1F2937; background:#fff; }
        .lbl  { font-size:7px; color:#6B7280; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
        .val  { font-size:13px; font-weight:700; color:#002452; margin-top:2px; }
        .val-g{ color:#006c49; }
        .val-r{ color:#EF4444; }
        .sub  { font-size:7px; color:#9CA3AF; margin-top:1px; }
        .stitle{ font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px;
                 color:#6B7280; padding-bottom:3px; border-bottom:1px solid #E5E7EB; margin-bottom:6px; }
        table.data { width:100%; border-collapse:collapse; }
        table.data thead th { background:#F8FAFC; font-size:7.5px; font-weight:700; text-transform:uppercase;
            color:#6B7280; padding:4px 6px; text-align:left; border-bottom:2px solid #E5E7EB; }
        table.data thead th.r { text-align:right; }
        table.data tbody td { padding:4px 6px; border-bottom:1px solid #F3F4F6; font-size:8.5px; }
        table.data tbody td.r { text-align:right; }
        table.data tbody tr:nth-child(even) td { background:#F9FAFB; }
        table.data tfoot td { padding:5px 6px; font-size:8.5px; font-weight:700; background:#F8FAFC; border-top:2px solid #E5E7EB; }
        table.data tfoot td.r { text-align:right; }
        .badge { display:inline-block; font-size:6.5px; font-weight:700; padding:1px 4px; border-radius:6px; text-transform:uppercase; }
        .b-red   { background:#fee2e2; color:#991b1b; }
        .b-green { background:#d1fae5; color:#065f46; }
        .b-yellow{ background:#fef3c7; color:#92400e; }
        .b-gray  { background:#F3F4F6; color:#6B7280; }
        .box { background:#F8FAFC; border:1px solid #E5E7EB; border-radius:6px; padding:10px 12px; }
    </style>
</head>
<body>

{{-- HEADER --}}
<table width="100%" style="background:#002452;margin-bottom:14px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding:14px 24px;">
            <div style="font-size:16px;font-weight:700;color:#fff;">EcoPoche — Rapport Comparatif</div>
            <div style="font-size:9px;color:#9ab0cc;margin-top:2px;">{{ $user->name }} &nbsp;·&nbsp; {{ $user->email }}</div>
            <div style="display:inline-block;background:#006c49;color:#fff;font-size:8px;font-weight:700;
                        padding:2px 8px;border-radius:12px;text-transform:uppercase;margin-top:4px;">{{ $periodeLabel }}</div>
        </td>
        <td style="padding:14px 24px;text-align:right;vertical-align:middle;">
            <div style="font-size:8px;color:#9ab0cc;">{{ now()->translatedFormat('d F Y') }}</div>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="padding:0 24px;">
<tr><td style="padding:0 24px;">

{{-- KPIs --}}
<p class="stitle" style="margin-bottom:8px;">Synthèse sur la période</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
    <tr>
        <td width="24%" style="padding:7px 9px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:4px;">
            <div class="lbl">Total Revenus</div>
            <div class="val">{{ number_format($totalRevenu,0,',',' ') }} <span style="font-size:8px;font-weight:400;color:#6B7280;">FCFA</span></div>
        </td>
        <td width="2%"></td>
        <td width="24%" style="padding:7px 9px;border:1px solid #EF4444;background:#fef2f2;border-radius:4px;">
            <div class="lbl">Total Dépenses</div>
            <div class="val val-r">{{ number_format($totalDepenses,0,',',' ') }} <span style="font-size:8px;font-weight:400;">FCFA</span></div>
        </td>
        <td width="2%"></td>
        <td width="24%" style="padding:7px 9px;border:1px solid #006c49;background:#f0fdf4;border-radius:4px;">
            <div class="lbl">Total Épargné</div>
            <div class="val val-g">{{ number_format($totalEpargne,0,',',' ') }} <span style="font-size:8px;font-weight:400;">FCFA</span></div>
        </td>
        <td width="2%"></td>
        <td width="22%" style="padding:7px 9px;border:1px solid #E5E7EB;background:#F8FAFC;border-radius:4px;">
            <div class="lbl">Taux d'épargne</div>
            <div class="val {{ $tauxEpargne>=15?'val-g':($tauxEpargne>=5?'':'val-r') }}">{{ $tauxEpargne }}%</div>
            <div class="sub">{{ $tauxEpargne>=15?'Excellent':($tauxEpargne>=5?'Correct':'Faible') }}</div>
        </td>
    </tr>
</table>

@php
    /* ---- Graphique comparatif SVG : calibré sur 460px (60% de 770px utile) ---- */
    $maxVal = max(collect($historique)->max('revenu'), collect($historique)->max('depenses'), 1);
    $cW=460; $cH=150;
    $pL=36; $pB=20; $pR=6; $pT=8;
    $iW=$cW-$pL-$pR; $iH=$cH-$pB-$pT;
    $n=count($historique);
    $gW=$iW/max($n,1);
    $bW=max(5,min(16,$gW/4));
    $gap=$bW*0.5;
    $yFn=fn($v,$m,$ih,$pt)=>$m==0?$pt+$ih:$pt+$ih-($v/$m)*$ih;
    $hFn=fn($v,$m,$ih)=>$m==0?0:max(1,($v/$m)*$ih);
@endphp

{{-- LIGNE 1 : Graphique comparatif + Top catégories --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
    <tr valign="top">
        {{-- Graphique comparatif --}}
        <td width="60%" style="padding-right:10px;">
            <div class="box">
                <div style="font-size:9px;font-weight:700;color:#1F2937;margin-bottom:6px;">
                    Comparatif {{ $periodeLabel }} — Revenus / Dépenses / Épargne
                </div>
                <div style="font-size:7.5px;color:#6B7280;margin-bottom:6px;">
                    <span style="color:#002452;font-weight:700;">&#9632;</span> Revenus &nbsp;
                    <span style="color:#EF4444;font-weight:700;">&#9632;</span> Dépenses &nbsp;
                    <span style="color:#006c49;font-weight:700;">&#9632;</span> Épargne
                </div>
                <svg width="{{ $cW }}" height="{{ $cH }}" xmlns="http://www.w3.org/2000/svg">
                    @for($gi=0;$gi<=4;$gi++)
                    @php $gy=$pT+($iH/4)*$gi; $gv=round($maxVal*(4-$gi)/4/1000); @endphp
                    <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $cW-$pR }}" y2="{{ $gy }}" stroke="#E5E7EB" stroke-width="0.5"/>
                    <text x="{{ $pL-3 }}" y="{{ $gy+3 }}" font-size="7" fill="#9CA3AF" text-anchor="end">{{ $gv }}k</text>
                    @endfor
                    <line x1="{{ $pL }}" y1="{{ $pT+$iH }}" x2="{{ $cW-$pR }}" y2="{{ $pT+$iH }}" stroke="#D1D5DB" stroke-width="1"/>
                    @foreach($historique as $idx=>$h)
                    @php
                        $cx=$pL+$gW*$idx+$gW/2;
                        $x1=$cx-$bW*1.5-$gap; $x2=$cx-$bW*0.5; $x3=$cx+$bW*0.5+$gap;
                        $hR=$hFn($h['revenu'],$maxVal,$iH);   $yR=$yFn($h['revenu'],$maxVal,$iH,$pT);
                        $hD=$hFn($h['depenses'],$maxVal,$iH); $yD=$yFn($h['depenses'],$maxVal,$iH,$pT);
                        $hE=$hFn($h['epargne'],$maxVal,$iH);  $yE=$yFn($h['epargne'],$maxVal,$iH,$pT);
                        $lp=explode(' ',$h['label']);
                        $sl=strtoupper(substr($lp[0]??'',0,3)).' '.($lp[1]??'');
                    @endphp
                    <rect x="{{ $x1 }}" y="{{ $yR }}" width="{{ $bW }}" height="{{ $hR }}" fill="#002452" fill-opacity="0.35" rx="1"/>
                    <rect x="{{ $x2 }}" y="{{ $yD }}" width="{{ $bW }}" height="{{ $hD }}" fill="#EF4444" fill-opacity="0.85" rx="1"/>
                    <rect x="{{ $x3 }}" y="{{ $yE }}" width="{{ $bW }}" height="{{ $hE }}" fill="#006c49" fill-opacity="0.9"  rx="1"/>
                    <text x="{{ $cx }}" y="{{ $pT+$iH+14 }}" font-size="6" fill="#9CA3AF" text-anchor="middle">{{ $sl }}</text>
                    @endforeach
                </svg>
            </div>
        </td>
        {{-- Top catégories --}}
        <td width="40%">
            <p class="stitle">Top dépenses / catégorie</p>
            @if($topCategories->isEmpty())
                <p style="color:#9CA3AF;font-size:8px;font-style:italic;margin-top:6px;">Aucune dépense.</p>
            @else
            @foreach($topCategories as $i=>$cat)
            @php $pct=$topCategories->first()['total']>0?round($cat['total']/$topCategories->first()['total']*100):0; @endphp
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:5px;">
                <tr>
                    <td style="font-size:8.5px;font-weight:600;">
                        <span style="color:{{ $cat['couleur'] }}">&#9679;</span> {{ $cat['nom'] }}
                    </td>
                    <td style="text-align:right;font-size:8px;color:#6B7280;white-space:nowrap;">
                        {{ number_format($cat['total'],0,',',' ') }} &nbsp;<strong>{{ $pct }}%</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:2px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#E5E7EB;border-radius:3px;height:6px;">
                            <tr><td width="{{ $pct }}%" style="background:{{ $cat['couleur'] }};border-radius:3px;height:6px;"></td><td></td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            @endforeach
            @endif

            {{-- Évolution épargne --}}
            @php
                $eps=array_column($historique,'epargne');
                $mxE=max(max($eps),1);
                $sW=270; $sH=90;
                $pl2=26; $pb2=16; $pr2=6; $pt2=6;
                $iw2=$sW-$pl2-$pr2; $ih2=$sH-$pb2-$pt2;
                $ne=count($eps); $pts2=[];
                foreach($eps as $ei=>$ev){
                    $px=$pl2+($ne>1?$iw2*$ei/($ne-1):$iw2/2);
                    $py=$pt2+$ih2-($mxE>0?($ev/$mxE)*$ih2:0);
                    $pts2[]="$px,$py";
                }
                $poly2=implode(' ',$pts2);
            @endphp
            <p class="stitle" style="margin-top:10px;">Évolution de l'épargne</p>
            <div class="box" style="padding:8px 10px;">
                <svg width="{{ $sW }}" height="{{ $sH }}" xmlns="http://www.w3.org/2000/svg">
                    @for($gi=0;$gi<=3;$gi++)
                    @php $gy3=$pt2+($ih2/3)*$gi; $gv3=round($mxE*(3-$gi)/3/1000,1); @endphp
                    <line x1="{{ $pl2 }}" y1="{{ $gy3 }}" x2="{{ $sW-$pr2 }}" y2="{{ $gy3 }}" stroke="#E5E7EB" stroke-width="0.5"/>
                    <text x="{{ $pl2-3 }}" y="{{ $gy3+3 }}" font-size="6" fill="#9CA3AF" text-anchor="end">{{ $gv3 }}k</text>
                    @endfor
                    @if(count($pts2)>=2)
                    @php
                        $lxe=explode(',',$pts2[count($pts2)-1])[0];
                        $fxe=explode(',',$pts2[0])[0];
                        $ble=$pt2+$ih2;
                        $areae=$poly2." $lxe,$ble $fxe,$ble";
                    @endphp
                    <polygon points="{{ $areae }}" fill="#006c49" fill-opacity="0.08"/>
                    <polyline points="{{ $poly2 }}" fill="none" stroke="#006c49" stroke-width="1.5" stroke-linejoin="round"/>
                    @foreach($eps as $ei=>$ev)
                    @php $dpx=$pl2+($ne>1?$iw2*$ei/($ne-1):$iw2/2); $dpy=$pt2+$ih2-($mxE>0?($ev/$mxE)*$ih2:0); @endphp
                    @if($ev>0)<circle cx="{{ $dpx }}" cy="{{ $dpy }}" r="2" fill="#006c49"/>@endif
                    @endforeach
                    @endif
                    @foreach($historique as $ei=>$h)
                    @php $lx3=$pl2+($ne>1?$iw2*$ei/($ne-1):$iw2/2); $lp3=explode(' ',$h['label']); @endphp
                    <text x="{{ $lx3 }}" y="{{ $sH-2 }}" font-size="5.5" fill="#9CA3AF" text-anchor="middle">{{ strtoupper(substr($lp3[0]??'',0,1)) }}</text>
                    @endforeach
                </svg>
            </div>
        </td>
    </tr>
</table>

{{-- LIGNE 2 : Tableau détail par mois (pleine largeur) --}}
<p class="stitle">Détail par mois</p>
<table class="data" width="100%">
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
            $tx=$h['revenu']>0?round($h['depenses']/$h['revenu']*100):0;
            $sc=match(true){
                $h['revenu']==0=>['l'=>'—',       'c'=>'b-gray'],
                $h['solde']<0  =>['l'=>'Dépassé', 'c'=>'b-red'],
                $tx>=70        =>['l'=>'Attention','c'=>'b-yellow'],
                default        =>['l'=>'Sain',     'c'=>'b-green'],
            };
        @endphp
        <tr>
            <td style="font-weight:600;">
                {{ $h['label'] }}
                @if($h['actif'])<span class="badge b-green">Actuel</span>@endif
            </td>
            <td class="r">{{ $h['revenu']>0?number_format($h['revenu'],0,',',' '):'—' }}</td>
            <td class="r" style="{{ $h['depenses']>0?'color:#EF4444;':'color:#9CA3AF;' }}">
                {{ $h['depenses']>0?number_format($h['depenses'],0,',',' '):'—' }}
            </td>
            <td class="r" style="{{ $h['solde']>=0?'color:#006c49;font-weight:700;':'color:#EF4444;font-weight:700;' }}">
                {{ $h['revenu']>0?(($h['solde']>=0?'+':'').number_format($h['solde'],0,',',' ')):'—' }}
            </td>
            <td class="r" style="color:#006c49;font-weight:600;">
                {{ $h['epargne']>0?number_format($h['epargne'],0,',',' '):'—' }}
            </td>
            <td class="r"><span class="badge {{ $sc['c'] }}">{{ $sc['l'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="r">{{ number_format($totalRevenu,0,',',' ') }}</td>
            <td class="r" style="color:#EF4444;">{{ number_format($totalDepenses,0,',',' ') }}</td>
            <td class="r" style="{{ ($totalRevenu-$totalDepenses)>=0?'color:#006c49;':'color:#EF4444;' }}">
                {{ number_format($totalRevenu-$totalDepenses,0,',',' ') }}
            </td>
            <td class="r" style="color:#006c49;">{{ number_format($totalEpargne,0,',',' ') }}</td>
            <td class="r" style="color:#002452;">{{ $tauxEpargne }}%</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top:16px;padding-top:6px;border-top:1px solid #E5E7EB;font-size:7.5px;color:#9CA3AF;text-align:center;">
    Rapport généré par EcoPoche le {{ now()->translatedFormat('d F Y à H:i') }}
    &nbsp;·&nbsp; Période : {{ $periodeLabel }}
</div>

</td></tr>
</table>

</body>
</html>
