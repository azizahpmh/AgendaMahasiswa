<?php
// Nama file untuk simpan data
$dataFile = "data.json";

// Fungsi untuk ambil data dari JSON
function load_data($file) {
    if (!file_exists($file)) {
        return ["jadwal" => [], "tugas" => []];
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// Fungsi untuk simpan data ke JSON
function save_data($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Ambil data awal
$data = load_data($dataFile);

// Tambah jadwal kuliah
if (isset($_POST['tambah_jadwal'])) {
    $mataKuliah = $_POST['mataKuliah'];
    $hari       = $_POST['hari'];
    $jam        = $_POST['jam'];
    $ruangan    = $_POST['ruangan'];
    $dosen      = $_POST['dosen'];
    $sks        = (int)$_POST['sks'];

    $data['jadwal'][] = [
    "mataKuliah" => $mataKuliah,
    "hari"       => $hari,
    "jam"        => $jam,
    "ruangan"    => $ruangan,
    "dosen"      => $dosen,
    "sks"        => $sks
    ];
save_data($dataFile, $data);

// cegah dobel data saat reload
header("Location: index.php");
exit;
}

// Tambah tugas
if (isset($_POST['tambah_tugas'])) {
    $tugas    = $_POST['tugas'];
    $deadline = $_POST['deadline'];

    $data['tugas'][] = [
    "tugas"    => $tugas,
    "deadline" => $deadline,
    "status"   => "Belum selesai"
    ];
save_data($dataFile, $data);

// cegah dobel data saat reload
header("Location: index.php");
exit;
}

// Update status tugas
if (isset($_GET['selesai'])) {
    $id = $_GET['selesai'];
    $data['tugas'][$id]['status'] = "Selesai";
    save_data($dataFile, $data);
}

// Hitung total SKS
$totalSKS = 0;
foreach ($data['jadwal'] as $j) {
    $totalSKS += $j['sks'];
}

// Filter jadwal hari ini
$hariSekarang = strtolower(date("l")); 
$jadwalHariIni = [];
foreach ($data['jadwal'] as $j) {
    if (strtolower($j['hari']) == $hariSekarang) {
        $jadwalHariIni[] = $j;
    }
}

// Peringatan tugas mendekati deadline
$peringatan = [];
$hariIni = strtotime(date("Y-m-d"));
foreach ($data['tugas'] as $t) {
    $deadline = strtotime($t['deadline']);
    $selisih = ($deadline - $hariIni) / (60*60*24);
    if ($selisih >= 0 && $selisih <= 3 && $t['status'] == "Belum selesai") {
        $peringatan[] = $t;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Agenda Kuliah Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>📅Agenda Mahasiswa</h1>

    <!-- Jadwal Kuliah -->
    <div class="card">
        <h2>📚Tambah Jadwal Kuliah</h2>
            <form method="POST">
                <input type="text" name="mataKuliah" placeholder="Mata Kuliah" required>
            
                    <select name="hari" required>
                        <option value="">-- Pilih Hari --</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
    
                <input type="time" name="jam" required>
                
                <input type="text" name="ruangan" placeholder="Ruangan" required>
                <input type="text" name="dosen" placeholder="Dosen Pengampu" required>
                
                <input type="number" name="sks" min="1" max="6" value="3" required>
                
                <button type="submit" name="tambah_jadwal">Tambah Jadwal</button>
            </form>

        <h3>Daftar Jadwal Kuliah</h3>
        <table border="1">
            <tr>
                <th>Mata Kuliah</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Ruangan</th>
                <th>Dosen</th>
                <th>SKS</th>
            </tr>
            <?php foreach ($data['jadwal'] as $j): ?>
            <tr>
                <td><?= $j['mataKuliah'] ?></td>
                <td><?= $j['hari'] ?></td>
                <td><?= $j['jam'] ?></td>
                <td><?= $j['ruangan'] ?></td>
                <td><?= $j['dosen'] ?></td>
                <td><?= $j['sks'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><b>Total SKS:</b> <?= $totalSKS ?></p>

        <h3>Jadwal Hari Ini (<?= date("l") ?>)</h3>
        <?php if ($jadwalHariIni): ?>
        <table border="1">
            <tr>
                <th>Mata Kuliah</th>
                <th>Jam</th>
                <th>Ruangan</th>
                <th>Dosen</th>
                <th>SKS</th>
            </tr>
            <?php foreach ($jadwalHariIni as $j): ?>
            <tr>
                <td><?= $j['mataKuliah'] ?></td>
                <td><?= $j['jam'] ?></td>
                <td><?= $j['ruangan'] ?></td>
                <td><?= $j['dosen'] ?></td>
                <td><?= $j['sks'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>Tidak ada jadwal hari ini.</p>
        <?php endif; ?>
    </div>

    <!-- Tugas -->
    <div class="card">
        <h2>📝Tambah Tugas</h2>
        <form method="POST">
            <input type="text" name="tugas" placeholder="Nama Tugas" required>
            <input type="date" name="deadline" required>
            <button type="submit" name="tambah_tugas">Tambah Tugas</button>
        </form>
        

        <h3>Daftar Tugas</h3>
        <table border="1">
            <tr>
                <th>Tugas</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($data['tugas'] as $i => $t): ?>
            <tr>
                <td><?= $t['tugas'] ?></td>
                <td><?= $t['deadline'] ?></td>
                <td><?= $t['status'] ?></td>
                <td>
                    <?php if ($t['status'] == "Belum selesai"): ?>
                        <a href="?selesai=<?= $i ?>">Tandai Selesai</a>
                    <?php else: ?>
                        ✅
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if ($peringatan): ?>
        <div class="alert">
            ⚠️ <b>Peringatan!</b> Ada tugas yang mendekati deadline :
            <ul>
                <?php foreach ($peringatan as $t): ?>
                    <li><?= $t['tugas'] ?> (Deadline: <?= $t['deadline'] ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</body>
