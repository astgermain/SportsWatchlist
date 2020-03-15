<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Brightfish\CachingGuzzle\Middleware;

class MLBGamesController extends Controller
{
	protected $client;

	public function __construct()
	{
        $store = app('cache')->store('database'); // Laravel
        $handler = new Middleware($store, 3600 * 24);
        $stack = HandlerStack::create();
        $stack->push($handler);

		$this->client = new Client([
            'handler' => $stack,
            'cache_ttl' => 3600 * 24, // 24 Hours
			'base_uri' => 'https://api.sportsdata.io/v3/',
			'headers' => [
				'Ocp-Apim-Subscription-Key' => '9460490d1fba48de84fa2c780aef429b'
			]
		]);
	}

    public function gamesByDate($date = null)
    {
    	if (is_null($date)) {
    		$date = now()->format('Y-M-d');
    	}

    	$requests = [
    		'teams' => $this->client->get('mlb/scores/json/AllTeams'),
            'stadiums' => $this->client->get('mlb/scores/json/Stadiums'),
    		'games' => $this->client->get("mlb/scores/json/GamesByDate/{$date}", ['cache' => false])
    	];

    	$responses = Promise\settle($requests)->wait();

    	$teams = collect(json_decode($responses['teams']['value']->getBody()->getContents()));
        $stadiums = collect(json_decode($responses['stadiums']['value']->getBody()->getContents()));
    	$games = collect(json_decode($responses['games']['value']->getBody()->getContents()));

    	$mappedGames = $games->map(function ($game) use ($teams, $stadiums) {
    		$homeTeam = $teams->filter(function ($team) use ($game) {
    			return $team->TeamID === $game->HomeTeamID;
    		})->first();

    		$awayTeam = $teams->filter(function ($team) use ($game) {
    			return $team->TeamID === $game->AwayTeamID;
    		})->first();

            $stadium = $stadiums->filter(function ($stadium) use ($game) {
                return $stadium->StadiumID === $game->StadiumID;
            })->first();

    		return [
    			'game_id' => $game->GameID,
    			'game_time' => $game->DateTime,
    			'home_team' => [
    				'id' => $game->HomeTeamID,
    				'name' => $game->HomeTeam,
    				'full_name' => $homeTeam->City.' '.$homeTeam->Name,
                    'rotation_number' => $game->HomeRotationNumber,
    				'runs' => $game->HomeTeamRuns,
                    'errors' => $game->HomeTeamErrors,
                    'hits' => $game->HomeTeamHits,
    				'money_line' => $game->HomeTeamMoneyLine,
    				'point_spread_money_line' => $game->PointSpreadHomeTeamMoneyLine,
                    'logo' => $homeTeam->WikipediaLogoUrl
    			],
    			'away_team' => [
    				'id' => $game->AwayTeamID,
    				'name' => $game->AwayTeam,
    				'full_name' => $awayTeam->City.' '.$awayTeam->Name,
                    'rotation_number' => $game->AwayRotationNumber,
    				'runs' => $game->AwayTeamRuns,
                    'errors' => $game->AwayTeamErrors,
                    'hits' => $game->AwayTeamHits,
    				'money_line' => $game->AwayTeamMoneyLine,
    				'point_spread_money_line' => $game->PointSpreadAwayTeamMoneyLine,
                    'logo' => $awayTeam->WikipediaLogoUrl
    			],
    			'innings' => $game->Innings,
                'stadium' => $stadium,
                'status' => $game->Status,
                'winningPitcher' => $game->WinningPitcher,
                'losingPitcher' => $game->LosingPitcher
    		];
    	});

    	return response()->json($mappedGames, 200);
    }
}