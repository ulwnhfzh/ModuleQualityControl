@extends('layouts.app')

@section('content')
<style>
    .dash { display: flex; flex-direction: column; gap: 20px; padding-bottom: 32px; width: 100%; }
    .dash-hero {
        border-radius: 16px; padding: 24px; color: #ffffff;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #38bdf8 100%);
        display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;
        box-shadow: 0 10px 25px rgba(30, 58, 138, .18);
    }
    .dash-hero small { display: block; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; opacity: .85; font-weight: 600; }
    .dash-hero h1 { margin: 4px 0 0; font-size: 28px; font-weight: 700; }
    .dash-hero p { margin: 4px 0 0; font-size: 14px; opacity: .9; }
    .dash-hero select {
        background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 10px;
        padding: 8px 12px; font-size: 14px; font-weight: 600; min-width: 110px;
    }

    .dash-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .dash-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-left-width: 4px; border-radius: 12px;
        padding: 16px; text-decoration: none; color: inherit; box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .dash-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15, 23, 42, .08); }
    .dash-card .lbl { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
    .dash-card .num { font-size: 32px; font-weight: 700; margin-top: 6px; line-height: 1.1; }
    .dash-card .sub { font-size: 12px; color: #94a3b8; margin-top: 6px; }
    .c-rose { border-left-color: #e11d48; } .c-rose .num { color: #e11d48; }
    .c-amber { border-left-color: #f59e0b; } .c-amber .num { color: #d97706; }
    .c-blue { border-left-color: #2563eb; } .c-blue .num { color: #2563eb; }

    .dash-split { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 16px; }
    .dash-panel {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }
    .dash-panel h2 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
    .dash-panel .desc { margin: 4px 0 0; font-size: 13px; color: #64748b; }
    .dash-head { display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: flex-start; }
    .dash-head select {
        border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 14px;
        font-weight: 600; color: #0f172a; background: #ffffff; min-width: 240px;
    }
    .legend { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; font-size: 12px; color: #475569; margin-top: 12px; }
    .legend i { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }
    .legend b { color: #0f172a; }
    .chart-box { position: relative; height: 320px; margin-top: 8px; }
    .chart-empty {
        position: absolute; inset: 0; display: none; align-items: center; justify-content: center;
        color: #64748b; font-size: 14px;
    }

    .top-item { margin-top: 14px; }
    .top-row { display: flex; justify-content: space-between; font-size: 13px; color: #0f172a; font-weight: 600; }
    .top-row span:last-child { color: #64748b; font-weight: 500; }
    .bar-track { height: 8px; background: #e2e8f0; border-radius: 999px; margin-top: 6px; overflow: hidden; }
    .bar-fill { height: 8px; border-radius: 999px; background: linear-gradient(90deg, #38bdf8, #2563eb); }

    .dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dash-table th {
        text-align: left; font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
        color: #64748b; padding: 10px 8px; border-bottom: 2px solid #e2e8f0;
    }
    .dash-table td { padding: 11px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .dash-table .nama { font-weight: 700; color: #0f172a; }
    .dash-table .sub { display: block; font-size: 11px; color: #94a3b8; font-weight: 500; }
    .pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .pill-rose { background: #ffe4e6; color: #be123c; }
    .pill-amber { background: #fef3c7; color: #b45309; }
    .link { font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none; }
    .link:hover { text-decoration: underline; }
    .empty { text-align: center; padding: 28px 8px; color: #64748b; font-size: 14px; }

    .act { list-style: none; margin: 0; padding: 0; }
    .act li { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .act li:last-child { border-bottom: none; }
    .act .waktu { color: #94a3b8; font-size: 12px; white-space: nowrap; }

    @media (max-width: 1100px) {
        .dash-split { grid-template-columns: 1fr; }
    }
    @media (max-width: 720px) {
        .dash-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-hero h1 { font-size: 22px; }
    }
</style>

<div class="dash">

    {{-- HEADER --}}
    <div class="dash-hero">
        <div>
            <small>Quality Control</small>
            <h1>Dashboard</h1>
            <p>Yang perlu ditindaklanjuti hari ini, dan pola order NG tahun {{ $tahun }}.</p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}">
            <label for="tahunSelect" style="display:none">Tahun laporan</label>
            <select id="tahunSelect" name="tahun" onchange="this.form.submit()">
                @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    {{-- KARTU RINGKASAN --}}
    <div class="dash-cards">
        <a href="{{ route('notifikasi.index') }}" class="dash-card c-rose">
            <div class="lbl">Jadwal terlambat</div>
            <div class="num">{{ $cards['jadwal_terlambat'] }}</div>
            <div class="sub">1 sampai 90 hari · {{ $cards['jadwal_lewat_90'] }} lewat 90 hari</div>
        </a>
        <a href="{{ route('notifikasi.index') }}" class="dash-card c-amber">
            <div class="lbl">Jatuh tempo</div>
            <div class="num">{{ $cards['jadwal_h5'] }}</div>
            <div class="sub">dalam 5 hari, termasuk hari ini</div>
        </a>
        <a href="{{ route('notifikasi.index') }}" class="dash-card c-rose">
            <div class="lbl">Mesin terlambat</div>
            <div class="num">{{ $cards['mesin_terlambat'] }}</div>
            <div class="sub">{{ $cards['mesin_cepat'] }} mesin jatuh tempo dalam 7 hari</div>
        </a>
        <a href="{{ route('list-up-ng.index', ['tahun' => $tahun]) }}" class="dash-card c-blue">
            <div class="lbl">Order NG belum ditutup</div>
            <div class="num">{{ $cards['ng_terbuka'] }}</div>
            <div class="sub">dari {{ $cards['ng_total'] }} order NG tahun {{ $tahun }}</div>
        </a>
    </div>

    {{-- GRAFIK PARETO + PENYUMBANG --}}
    <div class="dash-split">

        <section class="dash-panel">
            <div class="dash-head">
                <div>
                    <h2 id="paretoJudul">Grafik</h2>
                    <p class="desc" id="paretoDeskripsi"></p>
                </div>
                <select id="paretoPilih" aria-label="Pilih data grafik">
                    @foreach($pareto as $key => $p)
                        <option value="{{ $key }}" {{ $key === $defaultPareto ? 'selected' : '' }}>{{ $p['judul'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="legend">
                <span><i style="background:#7dd3fc"></i>Batang: jumlah</span>
                <span><i style="background:#f97316"></i>Garis: persentase kumulatif</span>
                <span style="margin-left:auto">Total: <b id="paretoTotal">0</b></span>
            </div>

            <div class="chart-box">
                <canvas id="paretoChart"></canvas>
                <p id="paretoKosong" class="chart-empty">Belum ada data untuk grafik ini.</p>
            </div>
        </section>

        <section class="dash-panel">
            <h2>Penyumbang terbesar</h2>
            <p class="desc">Lima teratas dari grafik yang dipilih, beserta persentasenya.</p>
            <div id="paretoTop"></div>
        </section>
    </div>

    {{-- PERLU TINDAKAN --}}
    <section class="dash-panel">
        <div class="dash-head">
            <div>
                <h2>Perlu tindakan hari ini</h2>
                <p class="desc">Lima item paling mendesak dari jadwal dan mesin, diurutkan dari yang paling terlambat.</p>
            </div>
            <a href="{{ route('notifikasi.index') }}" class="link">Lihat semua di halaman Notification</a>
        </div>

        @if(count($tindakan))
            <div style="overflow-x:auto; margin-top:12px;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width:22%">Jenis</th>
                            <th style="width:30%">Nama alat</th>
                            <th style="width:18%">Jatuh tempo</th>
                            <th style="width:18%">Keterangan</th>
                            <th style="width:12%; text-align:right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tindakan as $r)
                            <tr>
                                <td>{{ $r['jenis'] }}</td>
                                <td>
                                    <span class="nama">{{ $r['nama'] }}</span>
                                    @if($r['detail'])<span class="sub">{{ $r['detail'] }}</span>@endif
                                </td>
                                <td>{{ $r['jatuh_tempo'] }}</td>
                                <td>
                                    <span class="pill {{ $r['sisa'] < 0 ? 'pill-rose' : 'pill-amber' }}">{{ $r['keterangan'] }}</span>
                                </td>
                                <td style="text-align:right">
                                    <a class="link" href="{{ route('notifikasi.index') }}">Buka</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="empty">Tidak ada yang perlu ditindaklanjuti hari ini.</p>
        @endif
    </section>

    {{-- AKTIVITAS (admin) --}}
    @if($isAdmin)
        <section class="dash-panel">
            <h2>Aktivitas data terakhir</h2>
            <p class="desc">Perubahan data terbaru. Login dan logout tidak ditampilkan.</p>
            @if(count($aktivitas))
                <ul class="act" style="margin-top:10px;">
                    @foreach($aktivitas as $a)
                        <li>
                            <span>
                                <b>{{ $a['user'] }}</b>
                                {{ strtolower($a['aksi']) }} {{ $a['jenis'] }}
                                <span style="color:#64748b">· {{ $a['keterangan'] }}</span>
                            </span>
                            <span class="waktu">{{ $a['waktu'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="empty">Belum ada aktivitas data.</p>
            @endif
        </section>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const PARETO = @json($pareto);
    const pilih = document.getElementById('paretoPilih');
    let grafik = null;

    function tampilkan(kunci) {
        const d = PARETO[kunci];
        document.getElementById('paretoJudul').textContent = d.judul;
        document.getElementById('paretoDeskripsi').textContent = d.deskripsi;
        document.getElementById('paretoTotal').textContent = Number(d.total).toLocaleString('id-ID');

        // Penyumbang terbesar
        const daftar = document.getElementById('paretoTop');
        daftar.innerHTML = '';
        if (!d.labels.length) {
            daftar.innerHTML = '<p class="empty">Belum ada data.</p>';
        }
        d.labels.slice(0, 5).forEach((label, i) => {
            const persen = d.total ? Math.round(d.values[i] / d.total * 1000) / 10 : 0;
            daftar.insertAdjacentHTML('beforeend', `
                <div class="top-item">
                    <div class="top-row"><span>${i + 1}. ${label}</span><span>${d.values[i]} · ${persen}%</span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:${Math.min(persen, 100)}%"></div></div>
                </div>`);
        });

        // Grafik Pareto
        const kosong = document.getElementById('paretoKosong');
        const kanvas = document.getElementById('paretoChart');
        if (grafik) grafik.destroy();

        if (!d.labels.length) {
            kanvas.style.visibility = 'hidden';
            kosong.style.display = 'flex';
            return;
        }
        kanvas.style.visibility = 'visible';
        kosong.style.display = 'none';

        grafik = new Chart(kanvas, {
            data: {
                labels: d.labels,
                datasets: [
                    { type: 'bar', label: 'Jumlah', data: d.values, backgroundColor: '#7dd3fc', hoverBackgroundColor: '#38bdf8', borderRadius: 6, yAxisID: 'y' },
                    { type: 'line', label: 'Kumulatif %', data: d.kumulatif, borderColor: '#f97316', backgroundColor: '#f97316', pointRadius: 4, tension: 0.25, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.yAxisID === 'y1' ? ` ${ctx.parsed.y}%` : ` ${ctx.parsed.y}` } }
                },
                scales: {
                    x: { ticks: { autoSkip: false, maxRotation: 50, minRotation: 0 }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, max: 100, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => v + '%' } }
                }
            }
        });
    }

    pilih.addEventListener('change', () => tampilkan(pilih.value));
    tampilkan(pilih.value);
</script>
@endsection