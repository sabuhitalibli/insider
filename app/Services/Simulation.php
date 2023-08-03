<?php

namespace App\Services;

use App\Models\Season;
use App\Models\Team;
use App\Models\Week;
use Exception;
use Illuminate\Support\Collection;

class Simulation
{
    /**
     * @return int
     */
    private function getTotalWeeksCount(): int
    {
        $teamCount = Team::count();

        return ($teamCount - 1) * 2;
    }

    /**
     * @return Season
     * @throws Exception
     */
    private function getCurrentSeason(): Season
    {
        $currentSeason = Season::withCount(['weeks' => function ($query) {
            $query->where('status', 1);
        }])
            ->latest()
            ->first();

        $totalWeekCount = $this->getTotalWeeksCount();

        if ($currentSeason === null || $currentSeason->weeks_count === $totalWeekCount) {
            $currentSeason = $this->startNewSeason();
        }

        return $currentSeason;
    }

    /**
     * @return Season
     * @throws Exception
     */
    private function startNewSeason(): Season
    {
        $currentSeason = Season::withCount('weeks')->latest()->first();

        $totalWeekCount = $this->getTotalWeeksCount();

        if ($currentSeason !== null && $currentSeason->weeks_count < $totalWeekCount) {
            throw new Exception('Current season is still in progress');
        }

        $season = Season::create();

        $teams = Team::all();

        $games = [];

        foreach ($teams as $homeTeam) {
            foreach ($teams as $awayTeam) {
                if ($homeTeam->id == $awayTeam->id) {
                    continue;
                }

                $games[] = [
                    'home' => $homeTeam,
                    'away' => $awayTeam
                ];
            }
        }

        shuffle($games);

        $weeks = array_chunk($games, 2, true);

        foreach ($weeks as $index => $games) {
            $week = $season->weeks()->create(['number' => $index + 1]);
            foreach ($games as $game) {
                $week->games()->create([
                    'home_team_id' => $game['home']->id,
                    'away_team_id' => $game['away']->id,
                ]);
            }
        }

        return $season;
    }

    /**
     * @param Week $nextWeek
     * @return void
     */
    public function playNextWeek(Week $nextWeek): void
    {
        $nextWeek->loadMissing(['games']);

        foreach ($nextWeek->games as $game) {
            $game->update([
                'home_team_goal_count' => rand(0, 3),
                'away_team_goal_count' => rand(0, 3),
                'status' => 1
            ]);
        }

        $nextWeek->update(['status' => 1]);
    }

    /**
     * @param Collection<Week> $nextWeeks
     * @return array
     */
    public function playAllWeeks(Collection $nextWeeks): array
    {
        $results = [];

        foreach ($nextWeeks as $nextWeek) {
            $this->playNextWeek($nextWeek);

            $results[] = [
                'number' => $nextWeek->number,
                'games' => $this->getWeekGames($nextWeek)
            ];
        }

        return $results;
    }

    /**
     * @return  Week|null
     * @throws Exception
     */
    public function getCurrentWeek(): ?object
    {
        $currentSeason = $this->getCurrentSeason();

        return Week::with(['games'])
            ->where('season_id', $currentSeason->id)
            ->where('status', '=', 1)
            ->orderBy('number', 'desc')
            ->first();
    }

    /**
     * @return  Week
     * @throws Exception
     */
    public function getNextWeek(): object
    {
        $currentSeason = $this->getCurrentSeason();

        return Week::with(['games'])
            ->where('season_id', $currentSeason->id)
            ->where('status', '=', 0)
            ->orderBy('number')
            ->first();
    }

    /**
     * @return Collection<Week>
     * @throws Exception
     */
    public function getNextWeeks(): Collection
    {
        $currentSeason = $this->getCurrentSeason();

        return Week::with(['games'])->where('season_id', $currentSeason->id)
            ->where('status', '=', 0)
            ->orderBy('number')
            ->get();
    }

    /**
     * @return array
     */
    public function getCurrentSeasonData(): array
    {
        $currentSeason = $this->getCurrentSeason();

        $currentSeason->loadMissing(['weeks' => function ($query) {
            $query->where('status', 1);
        }]);

        $weekIDs = $currentSeason->weeks->pluck('id');

        $teams = Team::with(['homeGames' => function ($query) use ($weekIDs) {
            $query->where('status', 1)->whereIn('week_id', $weekIDs);
        }, 'awayGames' => function ($query) use ($weekIDs) {
            $query->where('status', 1)->whereIn('week_id', $weekIDs);
        }])->get();

        $data = [];

        foreach ($teams as $team) {
            $won = 0;
            $lost = 0;
            $drawn = 0;
            $goalsFor = 0;
            $goalsAgainst = 0;

            foreach ($team->homeGames as $homeGame) {
                $goalsFor += $homeGame->home_team_goal_count;
                $goalsAgainst += $homeGame->away_team_goal_count;

                if ($homeGame->home_team_goal_count > $homeGame->away_team_goal_count) {
                    $won++;
                } elseif ($homeGame->home_team_goal_count < $homeGame->away_team_goal_count) {
                    $lost++;
                } else {
                    $drawn++;
                }
            }

            foreach ($team->awayGames as $awayGame) {
                $goalsFor += $awayGame->away_team_goal_count;
                $goalsAgainst += $awayGame->home_team_goal_count;

                if ($awayGame->away_team_goal_count > $awayGame->home_team_goal_count) {
                    $won++;
                } elseif ($awayGame->away_team_goal_count < $awayGame->home_team_goal_count) {
                    $lost++;
                } else {
                    $drawn++;
                }
            }

            $points = ($won * 3) + $drawn;

            $data[] = [
                'name' => $team->name,
                'played' => $team->homeGames->count() + $team->awayGames->count(),
                'points' => $points,
                'won' => $won,
                'lost' => $lost,
                'drawn' => $drawn,
                'goal_difference' => $goalsFor - $goalsAgainst
            ];
        }

        usort($data, fn($a, $b) => $b['points'] <=> $a['points']);

        return $data;
    }

    /**
     * @param Week $week
     * @return array
     */
    public function getWeekGames(Week $week): array
    {
        $week->loadMissing(['games', 'games.homeTeam', 'games.awayTeam']);

        $data = [];

        foreach ($week->games as $game) {
            $data[] = [
                'home_team_name' => $game->homeTeam->name,
                'home_team_goal_count' => $game->home_team_goal_count,
                'away_team_name' => $game->awayTeam->name,
                'away_team_goal_count' => $game->away_team_goal_count
            ];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getPredictions(array $data): array
    {
        $totalPoints = 0;

        foreach ($data as $team) {
            $totalPoints += $team['points'];
        }

        $predictions = [];

        foreach ($data as $team) {
            $predictions[] = [
                'name' => $team['name'],
                'probability' => round(($team['points'] / $totalPoints) * 100, 2),
            ];
        }

        return $predictions;
    }

    public function shouldDisplayPrediction(): bool
    {
        $currentSeason = $this->getCurrentSeason();

        return $currentSeason->weeks_count == 5;
    }
}
