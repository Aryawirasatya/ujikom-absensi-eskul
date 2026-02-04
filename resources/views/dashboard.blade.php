@extends('layouts.app')

@section('content')

<div class="row">

  {{-- INFO USER --}}
  <div class="col-md-12 mb-4">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-1">Selamat Datang, {{ auth()->user()->name }}</h4>
        <span class="badge bg-primary">
          {{ auth()->user()->getRoleNames()->first() }}
        </span>
      </div>
    </div>
  </div>


  {{-- ADMIN --}}
  @role('admin')
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5>Manajemen User</h5>
        <p>Tambah, edit, hapus pengguna.</p>
        <button class="btn btn-primary btn-sm">Lihat</button>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5>Data Eskul</h5>
        <p>Kelola ekstrakurikuler sekolah.</p>
        <button class="btn btn-primary btn-sm">Lihat</button>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5>Laporan</h5>
        <p>Rekap absensi & statistik.</p>
        <button class="btn btn-primary btn-sm">Lihat</button>
      </div>
    </div>
  </div>
  @endrole


  {{-- PEMBINA --}}
  @role('pembina')
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5>Jadwal Eskul</h5>
        <p>Dummy jadwal kegiatan.</p>
        <ul>
          <li>Senin – Basket</li>
          <li>Rabu – Pramuka</li>
          <li>Jumat – Futsal</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5>Absensi</h5>
        <p>Data dummy kehadiran.</p>
        <p>Hadir: 10</p>
        <p>Izin: 2</p>
        <p>Alpha: 1</p>
      </div>
    </div>
  </div>
  @endrole


  {{-- SISWA --}}
  @role('siswa')
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <h5>Status Kehadiran</h5>
        <p>Data dummy absensi eskul kamu.</p>

        <table class="table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Eskul</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>01 Jan</td>
              <td>Basket</td>
              <td><span class="badge bg-success">Hadir</span></td>
            </tr>
            <tr>
              <td>05 Jan</td>
              <td>Futsal</td>
              <td><span class="badge bg-warning">Izin</span></td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>
  </div>
  @endrole

</div>

@endsection
