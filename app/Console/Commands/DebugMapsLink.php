<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Command SEMENTARA untuk debug kenapa ekstraksi koordinat dari link Google Maps
 * gagal. Jalankan lalu paste seluruh outputnya. Setelah masalah selesai, file ini
 * (dan baris registrasinya kalau pakai Laravel lama yang masih butuh daftar manual
 * di Kernel.php) boleh dihapus.
 *
 * Cara pakai:
 *   php artisan maps:debug "https://maps.app.goo.gl/z9uwTWR1toiKd7wN8"
 */
class DebugMapsLink extends Command
{
    protected $signature = 'maps:debug {link}';
    protected $description = 'Debug ekstraksi koordinat dari link Google Maps';

    public function handle(): int
    {
        $link = $this->argument('link');
        $this->line("Link input : {$link}");
        $this->line('');

        // 1) Cek ekstensi curl
        if (!function_exists('curl_init')) {
            $this->error('curl_init() TIDAK ADA. Ekstensi php-curl belum aktif di php.ini kamu (extension=curl).');
            return 1;
        }
        $this->info('✓ Ekstensi curl aktif.');

        // 2) Fetch dengan curl, follow redirect, capture semuanya
        $ch = curl_init($link);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; KP-System/1.0)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_VERBOSE        => false,
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
        curl_close($ch);

        $this->line('');
        $this->line("curl_errno       : {$errno}");
        $this->line("curl_error       : " . ($error ?: '(kosong)'));
        $this->line("HTTP code akhir  : {$httpCode}");
        $this->line("Jumlah redirect  : {$redirectCount}");
        $this->line("Effective URL    : {$effectiveUrl}");
        $this->line('Body length      : ' . ($body ? strlen($body) : 0) . ' bytes');
        $this->line('');

        if ($errno) {
            $this->error('cURL GAGAL total (kemungkinan besar ini penyebabnya). Lihat curl_error di atas.');
            $this->line('Penyebab paling umum di Windows: SSL certificate problem: unable to get local issuer certificate.');
            return 1;
        }

        // 3) Coba pola regex yang sama seperti InstansiController@extractLatLng,
        //    tapi dicoba satu-satu supaya ketahuan mana yang match dan dari mana (URL atau body).
        $patterns = [
            '@lat,lng'      => '/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',
            '?q= / ?query=' => '/[?&](?:q|query)=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',
            '?ll='          => '/[?&]ll=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',
            '!3d!4d'        => '/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/',
        ];

        $this->line('--- Cek pola di EFFECTIVE URL ---');
        $foundInUrl = false;
        foreach ($patterns as $name => $re) {
            if (preg_match($re, $effectiveUrl, $m)) {
                $this->info("✓ Pola '{$name}' KETEMU di URL: lat={$m[1]} lng={$m[2]}");
                $foundInUrl = true;
            }
        }
        if (!$foundInUrl) {
            $this->warn('✗ Tidak ada pola koordinat ketemu di effective URL.');
        }

        $this->line('');
        $this->line('--- Cek pola di BODY (isi HTML hasil redirect) ---');
        $foundInBody = false;
        if ($body) {
            foreach ($patterns as $name => $re) {
                if (preg_match($re, $body, $m)) {
                    $this->info("✓ Pola '{$name}' KETEMU di body: lat={$m[1]} lng={$m[2]}");
                    $foundInBody = true;
                }
            }
        }
        if (!$foundInBody) {
            $this->warn('✗ Tidak ada pola koordinat ketemu di body juga.');
            $this->line('');
            $this->line('Cuplikan 800 karakter pertama body (untuk didiagnosis manual):');
            $this->line('----------------------------------------------------');
            $this->line(substr((string) $body, 0, 800));
            $this->line('----------------------------------------------------');
        }

        return 0;
    }
}