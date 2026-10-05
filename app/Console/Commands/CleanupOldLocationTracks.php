<?php

namespace App\Console\Commands;

use App\Models\LocationTrack;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldLocationTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:cleanup {--days=30 : Jumlah hari retensi data jejak lokasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan data rekaman titik jejak lokasi (location tracks) yang melampaui batas retensi (default: 30 hari)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::today('Asia/Makassar')->subDays($days)->toDateString();

        $this->info("Menghapus data rekaman jejak lokasi sebelum tanggal: {$cutoffDate} (Retensi {$days} hari)...");

        $deletedCount = LocationTrack::where('date', '<', $cutoffDate)->delete();

        $this->info("Pembersihan selesai. Total {$deletedCount} data titik jejak lokasi lama berhasil dihapus.");

        return Command::SUCCESS;
    }
}
