{{-- pembina/laporan/pdf/detail-siswa.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "DejaVu Sans", Arial, sans-serif;
      font-size: 10px;
      color: #1f2937;
    }

    /* ================= HEADER ================= */

    .header {
      background: #1e3a5f;
      color: white;
      padding: 14px 20px;
    }

    .header-title {
      font-size: 16px;
      font-weight: bold;
    }

    .header-sub {
      font-size: 9px;
      opacity: .85;
      margin-top: 4px;
    }

    /* ================= META INFO ================= */

    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin: 12px 0 10px 0;
    }

    .meta-table td {
      padding: 4px 8px;
      font-size: 9px;
    }

    .meta-label {
      color: #6b7280;
    }

    .meta-value {
      font-weight: bold;
    }

    /* ================= SUMMARY BOX ================= */

    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }

    .summary-table td {
      border: 1px solid #e5e7eb;
      text-align: center;
      padding: 8px;
      background: #f9fafb;
    }

    .sum-val {
      font-size: 16px;
      font-weight: bold;
      color: #1e3a5f;
    }

    .sum-green {
      color: #16a34a;
    }

    .sum-red {
      color: #dc2626;
    }

    .sum-yellow {
      color: #d97706;
    }

    .sum-label {
      font-size: 8px;
      color: #6b7280;
    }

    /* ================= SECTION ================= */

    .section-title {
      font-size: 11px;
      font-weight: bold;
      margin: 8px 0 6px 0;
      border-bottom: 2px solid #1e3a5f;
      padding-bottom: 3px;
    }

    /* ================= TABLE ================= */

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th {
      background: #1e3a5f;
      color: white;
      padding: 6px;
      font-size: 9px;
      text-align: left;
    }

    .data-table td {
      padding: 5px 6px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 9px;
    }

    .data-table tr:nth-child(even) {
      background: #f9fafb;
    }

    /* ================= BADGES ================= */

    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 8px;
      font-weight: bold;
    }

    .badge-green {
      background: #dcfce7;
      color: #16a34a;
    }

    .badge-red {
      background: #fee2e2;
      color: #dc2626;
    }

    .badge-yellow {
      background: #fef9c3;
      color: #b45309;
    }

    .badge-blue {
      background: #dbeafe;
      color: #2563eb;
    }

    .badge-gray {
      background: #f3f4f6;
      color: #6b7280;
    }

    /* ================= FOOTER ================= */

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      border-top: 1px solid #e5e7eb;
      padding: 6px 20px;
      font-size: 8px;
      color: #9ca3af;
    }

    .footer table {
      width: 100%;
    }
  </style>
</head>

<body>

  {{-- ================= HEADER ================= --}}
  <div class="header">

    <div class="header-title">
      LAPORAN DETAIL KEHADIRAN SISWA
    </div>

    <div class="header-sub">
      {{ $eskul->name }} — Tahun Ajaran {{ $schoolYear->name ?? '-' }}
    </div>

  </div>

  {{-- ================= META SISWA ================= --}}
  <table class="meta-table">
    <tr>
      <td width="25%">
        <span class="meta-label">Nama Siswa</span><br>
        <span class="meta-value">{{ $siswa->name }}</span>
      </td>

      <td width="25%">
        <span class="meta-label">NISN</span><br>
        <span class="meta-value">{{ $siswa->nisn ?? '-' }}</span>
      </td>

      <td width="25%">
        <span class="meta-label">Ekstrakurikuler</span><br>
        <span class="meta-value">{{ $eskul->name }}</span>
      </td>

      <td width="25%">
        <span class="meta-label">Dicetak</span><br>
        <span class="meta-value">{{ $generated }}</span>
      </td>
    </tr>
  </table>

  {{-- ================= SUMMARY ================= --}}
  <table class="summary-table">
    <tr>
      <td>
        <div class="sum-val">{{ $detail['total'] }}</div>
        <div class="sum-label">Total Kegiatan</div>
      </td>

      <td>
        <div class="sum-val sum-green">{{ $detail['hadir'] }}</div>
        <div class="sum-label">Hadir</div>
      </td>

      <td>
        <div class="sum-val sum-yellow">{{ $detail['telat'] }}</div>
        <div class="sum-label">Telat</div>
      </td>

      <td>
        <div class="sum-val sum-red">{{ $detail['alpha'] }}</div>
        <div class="sum-label">Alpha</div>
      </td>

      <td>
        <div class="sum-val">{{ $detail['izin'] }}</div>
        <div class="sum-label">Izin</div>
      </td>

      <td>
        <div class="sum-val">{{ $detail['sakit'] }}</div>
        <div class="sum-label">Sakit</div>
      </td>

      <td>
        <div class="sum-val sum-green">{{ $detail['pct'] }}%</div>
        <div class="sum-label">% Kehadiran</div>
      </td>
    </tr>
  </table>

  {{-- ================= TABLE ================= --}}
  <div class="section-title">
    Riwayat Absensi Per Kegiatan
  </div>

  <table class="data-table">

    <thead>
      <tr>
        <th width="30">No</th>
        <th width="80">Tanggal</th>
        <th>Judul Kegiatan</th>
        <th width="70" style="text-align:center">Tipe</th>
        <th width="80" style="text-align:center">Status</th>
        <th width="60" style="text-align:center">Check-in</th>
        <th width="60" style="text-align:center">Check-out</th>
        <th width="60" style="text-align:center">Sumber</th>
      </tr>
    </thead>

    <tbody>

      @forelse($detail['rows'] as $i => $row)

        <tr>
          <td>{{ $i + 1 }}</td>

          <td>{{ $row['tanggal'] }}</td>

          <td>{{ $row['judul'] }}</td>

          <td style="text-align:center">
            <span class="badge {{ $row['tipe'] === 'routine' ? 'badge-blue' : 'badge-gray' }}">
              {{ $row['tipe'] === 'routine' ? 'Rutin' : 'Non-rutin' }}
            </span>
          </td>

          <td style="text-align:center">

            @php
              $statusClass = match($row['final_status']) {
                'hadir' => 'badge-green',
                'alpha' => 'badge-red',
                'telat' => 'badge-yellow',
                'izin', 'sakit' => 'badge-blue',
                default => 'badge-gray',
              };
            @endphp

            <span class="badge {{ $statusClass }}">
              {{ ucfirst($row['final_status']) }}
            </span>

            @if($row['final_status'] === 'hadir' && $row['checkin_status'] === 'late')
              <span class="badge badge-yellow">Telat</span>
            @endif

          </td>

          <td style="text-align:center">
            {{ $row['checkin_at'] }}
          </td>

          <td style="text-align:center">
            {{ $row['checkout_at'] }}
          </td>

          <td style="text-align:center">
            {{ strtoupper($row['sumber']) }}
          </td>
        </tr>

      @empty

        <tr>
          <td colspan="8" style="text-align:center;color:#9ca3af;padding:16px">
            Belum ada riwayat kehadiran
          </td>
        </tr>

      @endforelse

    </tbody>

  </table>

  {{-- ================= FOOTER ================= --}}
  <div class="footer">

    <table>
      <tr>
        <td>
          {{ $siswa->name }} — {{ $eskul->name }}
        </td>

        <td style="text-align:right">
          Dicetak {{ $generated }}
        </td>
      </tr>
    </table>

  </div>

</body>
</html>