<?php

namespace App\Libraries;

use App\Models\TariffModel;


class TravelExpenseCalculator
{
    protected $tariffModel;

    public function __construct()
    {
        $this->tariffModel = model(TariffModel::class);
    }

    public function calculate(string $destinationProvince, ?string $destinationCity, string $tingkatBiaya, int $durationDays): array
    {
        // Start building query
        $query = $this->tariffModel->where([
            'tingkat_biaya' => $tingkatBiaya,
            'tahun_berlaku' => date('Y'),
            'is_active'     => 1
        ])->like('province', $destinationProvince, 'both', null, true);

        if (!empty($destinationCity)) {
            // Try to match city as well. If it fails, fallback to province only.
            $cityTariff = clone $query;
            $tariff = $cityTariff->like('city', $destinationCity, 'both', null, true)->first();

            if (!$tariff) {
                // Fallback to province level tariff (city is null or doesn't match)
                $tariff = $query->groupStart()
                    ->where('city', null)
                    ->orWhere('city', '')
                    ->groupEnd()
                    ->first();
            }
        } else {
            // Fallback: match province where city is empty
            $tariff = $query->groupStart()
                ->where('city', null)
                ->orWhere('city', '')
                ->groupEnd()
                ->first();
        }

        // Ultimate fallback: just get the first matching province regardless of city
        if (!$tariff) {
            $tariff = $this->tariffModel->where([
                'tingkat_biaya' => $tingkatBiaya,
                'tahun_berlaku' => date('Y'),
                'is_active'     => 1
            ])->like('province', $destinationProvince, 'both', null, true)->first();
        }

        if (!$tariff) {
            throw new \Exception("Tarif untuk provinsi {$destinationProvince} (Tingkat {$tingkatBiaya}) tidak ditemukan.");
        }

        $uangHarian       = $tariff->uang_harian * $durationDays;
        $uangRepresentasi = $tariff->uang_representasi * $durationDays;
        $penginapan       = $tariff->penginapan * max(0, $durationDays - 1);

        $tiket           = 0;
        $transportDarat  = 0;
        $transportLokal  = 0;

        $totalBiaya = $uangHarian + $uangRepresentasi + $penginapan + $tiket + $transportDarat + $transportLokal;

        return [
            'uang_harian'       => $uangHarian,
            'uang_representasi' => $uangRepresentasi,
            'penginapan'        => $penginapan,
            'tiket'             => $tiket,
            'transport_darat'   => $transportDarat,
            'transport_lokal'   => $transportLokal,
            'total_biaya'       => $totalBiaya,
            'tariff_id'         => $tariff->id
        ];
    }
}
