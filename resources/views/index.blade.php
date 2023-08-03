<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Premier League</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>

<body>

<main id="app" class="container my-5">
    <div class="row" v-if="data">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            League Table
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col">Teams</th>
                                    <th scope="col">PTS</th>
                                    <th scope="col">P</th>
                                    <th scope="col">W</th>
                                    <th scope="col">D</th>
                                    <th scope="col">L</th>
                                    <th scope="col">GD</th>
                                </tr>
                                </thead>
                                <tbody>

                                <tr v-for="team in data.leaderboard">
                                    <th scope="row">@{{ team.name }}</th>
                                    <th class="text-center">@{{ team.points }}</th>
                                    <th class="text-center">@{{ team.played }}</th>
                                    <th class="text-center">@{{ team.won }}</th>
                                    <th class="text-center">@{{ team.drawn }}</th>
                                    <th class="text-center">@{{ team.lost }}</th>
                                    <th class="text-center">@{{ team.goal_difference }}</th>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <button @click="playAll" class="btn btn-dark">Play All</button>
                            <button @click="nextWeek" class="btn btn-primary">Next Week</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" v-if="data.weeks.length">
                    <div class="card">
                        <div class="card-header text-center">
                            Match Results
                        </div>
                        <div class="card-body" v-for="week in weeks">
                            <h5 class="card-title text-center">@{{ week.title }}</h5>

                            <div class="row" v-for="game in week.games">
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-10 text-center">@{{ game.home_team_name }}</div>
                                        <div class="col-md-2">@{{ game.home_team_goal_count }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">-</div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-2">@{{ game.away_team_goal_count }}</div>
                                        <div class="col-md-10 text-center">@{{ game.away_team_name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4" v-if="data.predictions.length">
            <div class="card">
                <div class="card-header">
                    Predictions
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                        <tr v-for="prediction in data.predictions">
                            <th scope="row">@{{ prediction.name }}</th>
                            <td class="text-end">@{{ prediction.probability }}%</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const {createApp} = Vue;

    createApp({
        data() {
            return {
                loading: false,
                data: null
            }
        },
        computed: {
            weeks() {
                this.data.weeks.forEach((item) => {
                    let title = '';

                    switch (item.number) {
                        case 1:
                            title = '1st';
                            break
                        case 2:
                            title = '2nd';
                            break
                        case 3:
                            title = '3rd';
                            break
                        case 4:
                            title = '4th';
                            break
                        case 5:
                            title = '5th';
                            break
                        case 6:
                            title = '6th';
                            break
                    }

                    title += " Week Match Results";

                    item.title = title;
                });

                return this.data.weeks;
            },
        },
        methods: {
            async currentWeek() {
                axios.get('{{ route('current_week') }}').then((response) => {
                    this.data = response.data;
                });
            },
            async nextWeek() {
                await axios.get('{{ route('next_week') }}').then((response) => {
                    this.data = response.data;
                });
            },
            async playAll() {
                await axios.get('{{ route('play_all') }}').then((response) => {
                    this.data = response.data;
                });
            }
        },
        mounted() {
            this.currentWeek();
        }
    }).mount('#app');
</script>

</body>

</html>
