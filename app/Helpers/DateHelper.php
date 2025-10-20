<?php
namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Menambahkan jumlah hari kerja ke tanggal tertentu
     * Hari kerja = Senin - Jumat (melewati Sabtu & Minggu)
     * 
     * @param Carbon|string $startDate Tanggal mulai
     * @param int $businessDays Jumlah hari kerja yang ingin ditambahkan
     * @return Carbon
     */
    public static function addBusinessDays($startDate, int $businessDays): Carbon
    {
        $date = Carbon::parse($startDate);
        $addedDays = 0;
        
        while ($addedDays < $businessDays) {
            $date->addDay();
            
            // Skip weekend (Sabtu = 6, Minggu = 0)
            if ($date->dayOfWeek !== Carbon::SATURDAY && $date->dayOfWeek !== Carbon::SUNDAY) {
                $addedDays++;
            }
        }
        
        return $date;
    }
    
    /**
     * Menghitung jumlah hari kerja antara dua tanggal
     * 
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return int
     */
    public static function countBusinessDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $businessDays = 0;
        
        while ($start->lte($end)) {
            if ($start->dayOfWeek !== Carbon::SATURDAY && $start->dayOfWeek !== Carbon::SUNDAY) {
                $businessDays++;
            }
            $start->addDay();
        }
        
        return $businessDays;
    }
}