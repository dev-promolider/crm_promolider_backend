<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;
use App\Models\GameType;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gameType1 = new GameType();
        $gameType1->title = "Ahorcado";
        $gameType1->save();

        $gameType2 = new GameType();
        $gameType2->title = "Pares de cartas";
        $gameType2->save();

        $gameType2 = new GameType();
        $gameType2->title = "Búho";
        $gameType2->save();

        $gameType2 = new GameType();
        $gameType2->title = "Completar texto";
        $gameType2->save();

        $gameType2 = new GameType();
        $gameType2->title = "Ordenar palabras";
        $gameType2->save();

        $gameType2 = new GameType();
        $gameType2->title = "Word Wheel";
        $gameType2->save();
    }
}
