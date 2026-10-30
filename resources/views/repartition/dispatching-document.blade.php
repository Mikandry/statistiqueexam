<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>{{ $meta['title'] }}</title>
<style>
    @page { margin: 22px 34px 130px 34px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111827; }
    .page { page-break-after: always; }
    .page:last-of-type { page-break-after: auto; }
    .head { text-align: center; line-height: 1.35; }
    .head img { width: 62px; height: 62px; margin: 4px 0; }
    .t1 { font-size: 16px; font-weight: bold; margin-top: 10px; }
    .t2 { font-size: 13px; font-weight: bold; margin: 4px 0 12px; }
    .axe { font-size: 12px; font-weight: bold; margin: 6px 0; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th, table.data td { border: 1px solid #64748b; padding: 4px 5px; }
    table.data th { background: #e2e8f0; text-align: center; }
    .r { text-align: right; } .c { text-align: center; }
    tr.pt td { background: #f1f5f9; font-weight: bold; }
    tr.arr td { background: #f8fafc; font-weight: bold; border-top: 2px solid #334155; }
    tr { page-break-inside: avoid; }
    .foot { position: fixed; left: 0; right: 0; bottom: -100px; height: 95px; }
    .foot table { width: 100%; border-collapse: collapse; }
    .foot td { width: 33%; vertical-align: top; padding: 0 6px; font-size: 10px; }
    .foot .sig { height: 42px; }
    .foot .nm { border-top: 1px dotted #111827; padding-top: 3px; min-height: 12px; }
</style></head><body>
<div class="foot"><table><tr>
    @foreach($meta['footer'] as $i => [$label, $name])
        <td style="text-align: {{ $i === 0 ? 'left' : ($i === 1 ? 'center' : 'right') }}"><b>{{ $label }}</b><div class="sig"></div><div class="nm">{{ $name !== '' ? $name : '' }}</div></td>
    @endforeach
</tr></table></div>

@foreach($pages as $page)
<div class="page">
    <div class="head">
        <div><b>REPOBLIKAN'I MADAGASIKARA</b></div>
        <div style="font-size:9px"><i>Fitiavana - Tanindrazana - Fandrosoana</i></div>
        @if($seal)<img src="{{ $seal }}" alt="">@endif
        <div><b>MINISTÈRE DE L'ÉDUCATION NATIONALE</b></div>
        <div><b>Service de l'Organisation des Examens</b></div>
        @if($meta['heading'])<div class="t1">{{ $meta['heading'] }}</div>@endif
        <div class="{{ $meta['heading'] ? 't2' : 't1' }}">{{ $meta['title'] }}</div>
    </div>
    <div class="axe">AXE : {{ $page['axe'] !== '' ? $page['axe'] : '........................' }}</div>
    @if($page['total'] === null)<div class="axe">POINT DE LARGAGE : {{ $page['points'][0]['point'] }}</div>@endif

    <table class="data">
        <thead><tr><th style="width:5%">N°</th><th>Nom du centre</th><th style="width:13%">Total candidats</th><th style="width:11%">Total salles</th><th style="width:10%">Colis</th><th style="width:17%">Observation</th></tr></thead>
        <tbody>
        @foreach($page['points'] as $p)
            @if($page['total'] !== null)<tr class="pt"><td colspan="6">POINT DE LARGAGE : {{ $p['point'] }}</td></tr>@endif
            @foreach($p['lines'] as $l)
                <tr><td class="c">{{ $l['n'] }}</td><td>{{ $l['nom'] }}</td><td class="r">{{ number_format($l['candidats'],0,',',' ') }}</td><td class="r">{{ number_format($l['salles'],0,',',' ') }}</td><td></td><td></td></tr>
            @endforeach
            <tr class="arr"><td colspan="2">ARRÊTAGE — {{ $p['point'] }}</td><td class="r">{{ number_format($p['candidats'],0,',',' ') }}</td><td class="r">{{ number_format($p['salles'],0,',',' ') }}</td>
                <td colspan="2">Colis : {{ $p['colis'] === null ? '........' : number_format($p['colis'],0,',',' ') }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endforeach
</body></html>
