<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemasukan dan Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="https://kit.fontawesome.com/958ff8a6ce.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row g-4 m-2">
            <div class="col-lg-6">
                <div class="card form-card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Total Saldo: Rp. {{ number_format($saldo->saldo ?? '0') }}</h2>
                        <hr class="text-white">
                        <form action="{{ route('simpan_data') }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-4"><label>Type</label></div>
                                <div class="col-8">
                                    <select name="type" id="type" class="form-control-plaintext" required>
                                        <option value="Pemasukan">Pemasukan</option>
                                        <option value="Pengeluaran">Pengeluaran</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-4"><label>Kategori</label></div>
                                <div class="col-8">
                                    <select name="kategori" id="kategori" class="form-control-plaintext" required>
                                        <option selected disabled value="">-pilih-</option>
                                        @foreach ($category as $cat)
                                            <option value="{{ $cat->category }}" data-type="{{ $cat->type }}">
                                                {{ $cat->category }}</option>
                                        @endforeach
                                        <option value="tambah">Tambah Kategori</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3" id="newCategoryRow" style="display: none;">
                                <div class="col-4"><label>Kategori Baru</label></div>
                                <div class="col-8">
                                    <input type="text" id="newCategory" name="newCategory"
                                        class="form-control-plaintext">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-4"><label>Jumlah</label></div>
                                <div class="col-8">
                                    <input type="number" id="jumlah" name="jumlah" class="form-control-plaintext"
                                        required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-4"><label>Tanggal</label></div>
                                <div class="col-8">
                                    <input type="date" id="tanggal" name="tanggal" class="form-control-plaintext"
                                        required>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-4"><label>Keterangan</label></div>
                                <div class="col-8">
                                    <textarea class="form-control-plaintext" name="keterangan" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-modern">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mt-5">
                    <h5 class="mb-3 text-light ">History</h5>
                    @foreach ($history as $his)
                        <div class="history-card" data-bs-toggle="popover" data-bs-trigger="hover"
                            data-bs-content="{{ $his->keterangan }}" data-bs-placement="top">
                            <div class="d-flex align-items-center">
                                <div class="history-icon">
                                    {!! $his->type === 'Pemasukan'
                                        ? '<i class="fa fa-arrow-down text-success"></i>'
                                        : '<i class="fa fa-arrow-up text-danger"></i>' !!}
                                </div>
                                <div class="flex-grow-1 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $his->category }}</h6>
                                            <small
                                                class="text-muted">{{ date('d M Y', strtotime($his->tanggal)) }}</small>
                                        </div>
                                        <div
                                            class="fw-bold {{ $his->type === 'Pemasukan' ? 'text-success' : 'text-danger' }}">
                                            {{ $his->type === 'Pemasukan' ? '+' : '-' }}Rp
                                            {{ number_format($his->total, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-center mt-3">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>Pemasukan</h5>
                                <canvas id="pemasukanChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Pengeluaran</h5>
                                <canvas id="pengeluaranChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    html: true
                })
            })
        });
        document.addEventListener("DOMContentLoaded", function() {
            var typeSelect = document.getElementById("type");
            var kategoriSelect = document.getElementById("kategori");
            var newCategoryRow = document.getElementById("newCategoryRow");

            kategoriSelect.addEventListener("change", function() {
                if (this.value === "tambah") {
                    newCategoryRow.style.display = "flex";
                } else {
                    newCategoryRow.style.display = "none";
                }
            });

            typeSelect.addEventListener("change", function() {
                var selectedType = this.value;
                var options = kategoriSelect.getElementsByTagName("option");

                for (var i = 0; i < options.length; i++) {
                    var option = options[i];
                    var type = option.getAttribute("data-type");

                    if (option.value === "tambah" || option.value === "" || type === selectedType) {
                        option.style.display = "";
                    } else {
                        option.style.display = "none";
                    }
                }

                kategoriSelect.value = "";
            });

            typeSelect.dispatchEvent(new Event("change"));
        });

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const jumlahInput = document.getElementById("jumlah");
            const typeSelect = document.getElementById("type");
            let totalSaldo = {{ $total_saldo ?? 0 }};

            form.addEventListener("submit", function(event) {
                event.preventDefault();

                let jumlah = parseInt(jumlahInput.value) || 0;
                let selectedType = typeSelect.value;

                if (selectedType === "Pengeluaran" && jumlah < totalSaldo) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Saldo Tidak Cukup!',
                        text: 'Anda tidak memiliki saldo untuk pengeluaran untuk ini.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Simpan',
                    text: "Yakin ingin menyimpan ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(form.action, {
                                method: "POST",
                                body: new FormData(form),
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'input[name="_token"]').value
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message,
                                    }).then(() => {
                                        location
                                            .reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: data.message,
                                    });
                                }
                            });
                    }
                });
            });
        });

        // ///////////////////////////////////////////////////////////////////////////

        var pemasukanData = @json($pemasukan);
        var pengeluaranData = @json($pengeluaran);

        var pemasukanLabels = pemasukanData.map(item => item.category);
        var pemasukanValues = pemasukanData.map(item => item.total);

        var pengeluaranLabels = pengeluaranData.map(item => item.category);
        var pengeluaranValues = pengeluaranData.map(item => item.total);

        var ctxPemasukan = document.getElementById('pemasukanChart').getContext('2d');
        new Chart(ctxPemasukan, {
            type: 'doughnut',
            data: {
                labels: pemasukanLabels,
                datasets: [{
                    label: 'Pemasukan',
                    data: pemasukanValues,
                    backgroundColor: '#28a745'
                }]
            }
        });

        var ctxPengeluaran = document.getElementById('pengeluaranChart').getContext('2d');
        new Chart(ctxPengeluaran, {
            type: 'doughnut',
            data: {
                labels: pengeluaranLabels,
                datasets: [{
                    label: 'Pengeluaran',
                    data: pengeluaranValues,
                    backgroundColor: '#dc3545'
                }]
            }
        });
    </script>
</body>

</html>
