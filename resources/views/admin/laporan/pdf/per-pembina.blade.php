<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1f2937; }

  .doc-header {
    background: linear-gradient(135deg, #78350f 0%, #d97706 100%);
    color: #fff;
    padding: 18px 24px 14px;
  }
  .doc-header .doc-title  { font-size: 16px; font-weight: bold; }
  .doc-header .doc-sub    { font-size: 9.5px; opacity: 0.85; margin-top: 3px; }
  .doc-header .meta-row   { margin-top: 10px; font-size: 9px; opacity: 0.9; }

  .stat-bar {
    background: #fffbeb;
    border-bottom: 2px solid #fde68a;
    padding: 10px 24px;
    display: flex;
    gap: 0;
  }
  .stat-pill { flex: 1; text-align: center; padding: 5px 4px; border-right: 1px solid #fde68a; }
  .stat-pill:last-child { border-right: none; }
  .stat-pill .val { font-size: 14px; font-weight: bold; color: #b45309; }
  .stat-pill .lbl { font-size: 8px; color: #92400e; text-transform: uppercase; letter-spacing: 0.3px; }

  .section { padding: 14px 24px; }
  .section-title {
    font-size: 11px; font-weight: bold; color: #92400e;
    border-bottom: 2px solid #d97706; padding-bottom: 4px; margin-bottom: 8px;
  }

  table { width: 100%; border-collapse: collapse; font-size: 9px; }
  thead tr th {
    background: #92400e; color: #fff;
    padding: 7px 8px; text-align: left; font-size: 8.5px; font-weight: bold;
  }
  thead tr th.center { text-align: center; }
  tbody tr td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
  tbody tr:nth-child(even) td { background: #fffbeb; }
  tbody tr td.center { text-align: center; }

  .pct-high { color: #16a34a; font-weight: bold; }
  .pct-mid  { color: #d97706; font-weight: bold; }
  .pct-low  { color: #dc2626; font-weight: bold; }

  .doc-footer {
    padding: 10px 24px; border-top: 1px solid #e5e7eb;
    display: flex; justify-content: space-between;
    font-size: 8px; color: #9ca3af; margin-top: 8px;
  }
</style>
</head>
<body>

<div class="doc-header">
  <div class="doc-title">Laporan Per Pembina — Tahun Ajaran {{ $schoolYear->name }}</div>
  <div class="doc-sub">Rekap kegiatan dan kehadiran berdasarkan pembina ekstrakurikuler</div>
  <div class="meta-row">Dicetak: {{ now()->format('d F Y, H:i') }} &nbsp;|&nbsp; Total Pembina: {{ count($pembinaReport) }}</div>
</div>

<div class="stat-bar">
  <div class="stat-pill">
    <div class="val">{{ count($pembinaReport) }}</div>
    <div class="lbl">Total Pembina</div>
  </div>
  <div class="stat-pill">
    <div class="val">{{ collect($pembinaReport)->sum('total_kegiatan') }}</div>
    <div class="lbl">Total Kegiatan</div>
  </div>
  <div class="stat-pill">
    <div class="val">{{ collect($pembinaReport)->sum('selesai') }}</div>
    <div class="lbl">Selesai</div>
  </div>
  <div class="stat-pill">
    @php $avgPct = collect($pembinaReport)->avg('pct'); @endphp
    <div class="val">{{ round($avgPct, 1) }}%</div>
    <div class="lbl">Rata-rata Kehadiran</div>
  </div>
</div>

<div class="section">
  <div class="section-title">Daftar Pembina Ekstrakurikuler</div>
  <table>
    <thead>
      <tr>
        <th style="width:4%">#</th>
        <th style="width:24%">Nama Pembina</th>
        <th style="width:28%">Eskul Diampu</th>
        <th class="center" style="width:12%">Total Kegiatan</th>
        <th class="center" style="width:10%">Selesai</th>
        <th class="center" style="width:10%">Dibatalkan</th>
        <th class="center" style="width:12%">% Kehadiran</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pembinaReport as $i => $p)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $p['nama'] }}</td>
          <td>{{ $p['eskuls'] }}</td>
          <td class="center">{{ $p['total_kegiatan'] }}</td>
          <td class="center">{{ $p['selesai'] }}</td>
          <td class="center">{{ $p['cancelled'] }}</td>
          <td class="center">
            @php $pct = (float)$p['pct']; @endphp
            <span class="{{ $pct >= 75 ? 'pct-high' : ($pct >= 50 ? 'pct-mid' : 'pct-low') }}">
              {{ $p['pct'] }}%
            </span>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="center" style="padding:20px;color:#9ca3af;">Tidak ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="doc-footer">
  <span>Dokumen ini dicetak otomatis oleh sistem.</span>
  <span>Halaman <span class="pagenum"></span></span>
</div>

</body>
</html>