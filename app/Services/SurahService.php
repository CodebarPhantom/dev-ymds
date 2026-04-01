<?php

namespace App\Services;

use App\Models\Surah;

class SurahService
{
    public function getAllSurahs()
    {
        return Surah::get();
    }

    public function getAllSurahForSelect()
    {
        return Surah::get()
            ->map(function ($surah) {
                return [
                    'id' => $surah->id,
                    'label' => $surah->name . ' - ' . $surah->name_latin,
                ];
            });
    }
}
