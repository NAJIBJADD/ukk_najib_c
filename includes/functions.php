<?php
// functions.php - perbaikan dengan pengecekan fungsi dan zona waktu

if (!function_exists('format_tanggal_indonesia')) {
    function format_tanggal_indonesia($datetime) {
        if (empty($datetime)) return '-';
        $timestamp = strtotime($datetime);
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $hari[date('w', $timestamp)] . ', ' . date('j', $timestamp) . ' ' . $bulan[date('n', $timestamp)-1] . ' ' . date('Y', $timestamp) . ' ' . date('H:i', $timestamp);
    }
}

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        if (empty($datetime)) return '-';
        
        // Set zona waktu Asia/Jakarta (sesuaikan dengan zona waktu server Anda)
        $timezone = new DateTimeZone('Asia/Jakarta');
        $now = new DateTime('now', $timezone);
        $ago = new DateTime($datetime, $timezone);
        $diff = $now->diff($ago);

        $isFuture = ($diff->invert == 0); // $datetime lebih besar dari now?

        if ($isFuture) {
            // Masa depan (datetime > now)
            if ($diff->y > 0) return "dalam " . $diff->y . " tahun";
            if ($diff->m > 0) return "dalam " . $diff->m . " bulan";
            if ($diff->d > 0) {
                if ($diff->d == 1) return "besok";
                return "dalam " . $diff->d . " hari";
            }
            if ($diff->h > 0) return "dalam " . $diff->h . " jam";
            if ($diff->i > 0) return "dalam " . $diff->i . " menit";
            return "sebentar lagi";
        } else {
            // Masa lalu (datetime < now)
            if ($diff->y > 0) return $diff->y . " tahun yang lalu";
            if ($diff->m > 0) return $diff->m . " bulan yang lalu";
            if ($diff->d > 0) {
                if ($diff->d == 1) return "kemarin";
                return $diff->d . " hari yang lalu";
            }
            if ($diff->h > 0) return $diff->h . " jam yang lalu";
            if ($diff->i > 0) return $diff->i . " menit yang lalu";
            return "baru saja";
        }
    }
}
?>