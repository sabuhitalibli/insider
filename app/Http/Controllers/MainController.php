<?php

namespace App\Http\Controllers;

use App\Services\Simulation;

class MainController extends Controller
{
    private Simulation $simulation;

    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
    }

    public function index()
    {
        return view('index');
    }

    public function currentWeek()
    {
        $currentSeasonData = $this->simulation->getCurrentSeasonData();

        $currentWeek = $this->simulation->getCurrentWeek();

        $weeks = [];

        if ($currentWeek !== null) {
            $weeks[] = [
                'number' => $currentWeek->number,
                'games' => $this->simulation->getWeekGames($currentWeek)
            ];
        }

        $predictions = [];

        if ($this->simulation->shouldDisplayPrediction()) {
            $predictions = $this->simulation->getPredictions($currentSeasonData);
        }

        return response()->json([
            'leaderboard' => $currentSeasonData,
            'weeks' => $weeks,
            'predictions' => $predictions
        ]);
    }

    public function nextWeek()
    {
        $nextWeek = $this->simulation->getNextWeek();

        $this->simulation->playNextWeek($nextWeek);

        $currentSeasonData = $this->simulation->getCurrentSeasonData();

        $weeks[] = [
            'number' => $nextWeek->number,
            'games' => $this->simulation->getWeekGames($nextWeek)
        ];

        $predictions = [];

        if ($this->simulation->shouldDisplayPrediction()) {
            $predictions = $this->simulation->getPredictions($currentSeasonData);
        }

        return response()->json([
            'leaderboard' => $currentSeasonData,
            'weeks' => $weeks,
            'predictions' => $predictions
        ]);
    }

    public function playAll()
    {
        $nextWeeks = $this->simulation->getNextWeeks();

        $weeks = $this->simulation->playAllWeeks($nextWeeks);

        $currentSeasonData = $this->simulation->getCurrentSeasonData();

        return response()->json([
            'leaderboard' => $currentSeasonData,
            'weeks' => $weeks,
            'predictions' => []
        ]);
    }
}
