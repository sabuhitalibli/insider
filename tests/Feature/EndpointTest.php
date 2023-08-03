<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndpointTest extends TestCase
{
    public function test_current_week_endpoint_returns_a_successful_response(): void
    {
        $response = $this->get(route('current_week'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'leaderboard' => [
                    '*' => [
                        'name',
                        'played',
                        'points',
                        'won',
                        'lost',
                        'drawn',
                        'goal_difference'
                    ]
                ],
                'weeks' => [
                    '*' => [
                        'number',
                        'games' => [
                            '*' => [
                                'home_team_name',
                                'home_team_goal_count',
                                'away_team_name',
                                'away_team_goal_count',
                            ]
                        ]
                    ]
                ],
                'predictions' => [
                    '*' => [
                        'name',
                        'probability'
                    ]
                ]
            ]);

        $this->assertDatabaseCount('seasons', 1);
        $this->assertDatabaseCount('weeks', 6);
        $this->assertDatabaseCount('games', 12);
    }

    public function test_next_week_endpoint_returns_a_successful_response(): void
    {
        $response = $this->get(route('next_week'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'leaderboard' => [
                    '*' => [
                        'name',
                        'played',
                        'points',
                        'won',
                        'lost',
                        'drawn',
                        'goal_difference'
                    ]
                ],
                'weeks' => [
                    '*' => [
                        'number',
                        'games' => [
                            '*' => [
                                'home_team_name',
                                'home_team_goal_count',
                                'away_team_name',
                                'away_team_goal_count',
                            ]
                        ]
                    ]
                ],
                'predictions' => [
                    '*' => [
                        'name',
                        'probability'
                    ]
                ]
            ]);

        $this->assertDatabaseCount('seasons', 1);
        $this->assertDatabaseCount('weeks', 6);
        $this->assertDatabaseCount('games', 12);
    }

    public function test_play_all_endpoint_returns_a_successful_response(): void
    {
        $response = $this->get(route('play_all'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'leaderboard' => [
                    '*' => [
                        'name',
                        'played',
                        'points',
                        'won',
                        'lost',
                        'drawn',
                        'goal_difference'
                    ]
                ],
                'weeks' => [
                    '*' => [
                        'number',
                        'games' => [
                            '*' => [
                                'home_team_name',
                                'home_team_goal_count',
                                'away_team_name',
                                'away_team_goal_count',
                            ]
                        ]
                    ]
                ],
                'predictions' => [
                    '*' => [
                        'name',
                        'probability'
                    ]
                ]
            ]);

        $this->assertDatabaseCount('seasons', 2);
        $this->assertDatabaseCount('weeks', 12);
        $this->assertDatabaseCount('games', 24);
    }
}
