<?php

/**
 * Forum Controller
 */

namespace App\Http\Controllers;

use App\Model\Fixture;
use App\Model\FixtureScorcard;
use App\Model\GamePoint;
use App\Model\PlayerPoint;
use App\Model\Player;
use App\Model\Team;
use App\Model\TeamOfTheWeek;
use App\Model\Grade;
use App\Model\FeedbackFixturePoint;
use Illuminate\Http\Request;

class ScoreCardController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }

    function scorecardDetail(Request $request)
    {
        $fixtureId = $request->fixtureId;
        $result = FixtureScorcard::leftjoin('players', 'fixture_scorecards.player_id', '=', 'players.id')
            ->select('fixture_scorecards.*', 'players.full_name as player_name', 'players.position as player_position')
            ->where('fixture_scorecards.fixture_id', $fixtureId)
            ->orderBy('id', 'ASC')
            ->get();
        $playerIds = FixtureScorcard::where('fixture_id', $fixtureId)->pluck('player_id')->toArray();
        $playerData = Player::whereIn('id', $playerIds)->orderBy('full_name', 'ASC')->select('full_name', 'id')->get();
        $map = $playerData->map(function ($item) {

            $data['value'] = $item->id;
            $data['text'] = $item->full_name;
            return $data;
        });
        $fixtureDetails = Fixture::where('id', $fixtureId)->first();
        $potmValue = !empty($fixtureDetails->potm) ? @unserialize($fixtureDetails->potm) : [];

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['player_list'] = $map;
        $data['fixtureDetails'] = $fixtureDetails;
        return response()->json($data);
    }


    public function editFixtureScorcard(Request $request)
    {
        $fixture_id = $request->fixtureId;
        // $inning = $request->inning;
        $thisData = $request->all();
        if (!empty($thisData)) {

            $fixtureDetails = Fixture::where('id', $fixture_id)->first();
            $getGradeFromFixture = GamePoint::where('grade_id', $fixtureDetails->grade)
                ->where('club', $fixtureDetails->club)
                ->first();

            $getPaidGrade = Grade::where('club', $this->userDetail->id)->where('is_paid', 1)->first();

            if (!empty($getGradeFromFixture) && ($getPaidGrade->is_paid == 1)) {

                $gamePointsValue = GamePoint::whereIn('attribute_key', array(
                    'rs',
                    'fours',
                    'sixes',
                    'wks',
                    'mdns',
                    'cs',
                    'cwks',
                    'sts',
                    'rods',
                    'roas',
                    'dks',
                    'mtch',
                    '30rs',
                    '50rs',
                    '75rs',
                    '100rs',
                    '200rs',
                    '3wks',
                    '4wks',
                    '5wks',
                    '6wks',
                    '7wks',
                    '8pluswks',
                    '0to4econs',
                    '4to6econs',
                    '6plusecons',
                    'ovrs',
                    'hattrick',
                ))
                    ->where('club', $fixtureDetails->club)
                    ->where('grade_id', $fixtureDetails->grade)
                    ->get();
            } else {

                $gamePointsValue = GamePoint::whereIn('attribute_key', array(
                    'rs',
                    'fours',
                    'sixes',
                    'wks',
                    'mdns',
                    'cs',
                    'cwks',
                    'sts',
                    'rods',
                    'roas',
                    'dks',
                    'mtch',
                    '30rs',
                    '50rs',
                    '75rs',
                    '100rs',
                    '200rs',
                    '3wks',
                    '4wks',
                    '5wks',
                    '6wks',
                    '7wks',
                    '8pluswks',
                    '0to4econs',
                    '4to6econs',
                    '6plusecons',
                    'ovrs',
                    'hattrick',
                ))->where('club', $fixtureDetails->club)
                    ->where('grade_id', 0)
                    ->get();

                if ($gamePointsValue) {
                    $gamePointsValue = GamePoint::whereIn('attribute_key', array(
                        'rs',
                        'fours',
                        'sixes',
                        'wks',
                        'mdns',
                        'cs',
                        'cwks',
                        'sts',
                        'rods',
                        'roas',
                        'dks',
                        'mtch',
                        '30rs',
                        '50rs',
                        '75rs',
                        '100rs',
                        '200rs',
                        '3wks',
                        '4wks',
                        '5wks',
                        '6wks',
                        '7wks',
                        '8pluswks',
                        '0to4econs',
                        '4to6econs',
                        '6plusecons',
                        'ovrs',
                        'hattrick',
                    ))->where('club', $fixtureDetails->club)
                        ->where('grade_id', 0)
                        ->get();
                }
            }
            $newArr = [];
            $imageName = "";
            foreach ($thisData['data'] as $key => $value) {
                //1=>BATS;2=>Bowler;3=>AR;4=>WK;
                $playerType = Player::where('id', $value['player_id'])->first();
                if ($playerType->position == 1 && !empty($gamePointsValue)) {
                    foreach ($gamePointsValue as $key2 => $value2) {
                        $newArr[$value2->attribute_key] = $value2->bats;
                    }
                } elseif ($playerType->position == 2 && !empty($gamePointsValue)) {
                    foreach ($gamePointsValue as $key2 => $value2) {
                        $newArr[$value2->attribute_key] = $value2->bowler;
                    }
                } elseif ($playerType->position == 3 && !empty($gamePointsValue)) {
                    foreach ($gamePointsValue as $key2 => $value2) {
                        $newArr[$value2->attribute_key] = $value2->ar;
                    }
                } elseif ($playerType->position == 4 && !empty($gamePointsValue)) {
                    foreach ($gamePointsValue as $key2 => $value2) {
                        $newArr[$value2->attribute_key] = $value2->wk;
                    }
                }
                $obj = FixtureScorcard::find($value['id']);
                $obj->rs = !empty($value['rs']) ? $value['rs'] : 0;
                $obj->fours = !empty($value['fours']) ? $value['fours'] : 0;
                $obj->sixes = !empty($value['sixes']) ? $value['sixes'] : 0;
                $obj->wks = !empty($value['wks']) ? $value['wks'] : 0;
                $obj->mdns = !empty($value['mdns']) ? $value['mdns'] : 0;
                $obj->cs = !empty($value['cs']) ? $value['cs'] : 0;
                $obj->cwks = !empty($value['cwks']) ? $value['cwks'] : 0;
                $obj->sts = !empty($value['sts']) ? $value['sts'] : 0;
                $obj->rods = !empty($value['rods']) ? $value['rods'] : 0;
                $obj->roas = !empty($value['roas']) ? $value['roas'] : 0;
                $obj->dks = !empty($value['dks']) ? $value['dks'] : 0;
                $obj->hattrick = !empty($value['hattrick']) ? $value['hattrick'] : 0;
                $obj->run = !empty($value['run']) ? $value['run'] : 0;
                $obj->overs = !empty($value['overs']) ? $value['overs'] : 0;
                $rs = $fours = $sixes = $wks = $mdns = $cs = $cwks = $sts = $rods = $roas = $dks = $match = $extraWkcts = 0;

                if (!empty($newArr['rs']) && $value['rs']) {
                    $rs = $newArr['rs'] * $value['rs'];
                }
                if (!empty($newArr['fours']) && $value['fours']) {
                    $fours = $newArr['fours'] * $value['fours'];
                }
                if (!empty($newArr['sixes']) && $value['sixes']) {
                    $sixes = $newArr['sixes'] * $value['sixes'];
                }
                if (!empty($newArr['wks']) && $value['wks']) {
                    $wks = $newArr['wks'] * $value['wks'];
                }
                if (!empty($newArr['mdns']) && $value['mdns']) {
                    $mdns = $newArr['mdns'] * $value['mdns'];
                }
                if (!empty($newArr['cs']) && $value['cs']) {
                    $cs = $newArr['cs'] * $value['cs'];
                }
                if (!empty($newArr['cwks']) && $value['cwks']) {
                    $cwks = $newArr['cwks'] * $value['cwks'];
                }
                if (!empty($newArr['sts']) && $value['sts']) {
                    $sts = $newArr['sts'] * $value['sts'];
                }
                if (!empty($newArr['rods']) && $value['rods']) {
                    $rods = $newArr['rods'] * $value['rods'];
                }
                if (!empty($newArr['roas']) && $value['roas']) {
                    $roas = $newArr['roas'] * $value['roas'];
                }
                if (!empty($newArr['dks']) && $value['dks']) {
                    $dks = $newArr['dks'] * $value['dks'];
                }

                if ((!empty($newArr['30rs'])) && ($value['rs'] >= 30 && $value['rs'] < 50)) {
                    $rs = $rs + $newArr['30rs'];
                }

                if ((!empty($newArr['50rs'])) && ($value['rs'] >= 50 && $value['rs'] < 75)) {
                    $rs = $rs + $newArr['50rs'];
                }
                if ((!empty($newArr['75rs'])) && ($value['rs'] >= 75 && $value['rs'] < 100)) {
                    $rs = $rs + $newArr['75rs'];
                }

                if ((!empty($newArr['100rs'])) && ($value['rs'] >= 100 && $value['rs'] < 200)) {
                    $rs = $rs + $newArr['100rs'];
                }

                if ((!empty($newArr['200rs'])) && ($value['rs'] >= 200)) {
                    $rs = $rs + $newArr['200rs'];
                }

                if (!empty($value['wks'])) {

                    if ((!empty($newArr['3wks'])) && ($value['wks'] > 2) && ($value['wks'] <= 3)) {
                        $extraWkcts = $newArr['3wks'];
                    }
                    if ((!empty($newArr['4wks'])) && ($value['wks'] >= 4) && ($value['wks'] < 5)) {
                        $extraWkcts = $extraWkcts + $newArr['4wks'];
                    }
                    if ((!empty($newArr['5wks'])) && ($value['wks'] >= 5) && ($value['wks'] < 6)) {
                        $extraWkcts = $extraWkcts + $newArr['5wks'];
                    }
                    if ((!empty($newArr['6wks'])) && ($value['wks'] >= 6) && ($value['wks'] < 7)) {
                        $extraWkcts = $extraWkcts + $newArr['6wks'];
                    }

                    if ((!empty($newArr['7wks'])) && ($value['wks'] >= 7) && ($value['wks'] < 8)) {
                        $extraWkcts = $extraWkcts + $newArr['7wks'];
                    }

                    if ((!empty($newArr['8pluswks'])) && ($value['wks'] >= 8)) {;
                        $extraWkcts = $extraWkcts + $newArr['8pluswks'];
                    }
                }
                /*hat-Trick*/
                $hattrick = 0;
                if (!empty($newArr['mtch'])) {
                    $match = $newArr['mtch'];
                }
                if ((!empty($newArr['hattrick'])) && !empty($value['hattrick'])) {
                    $hattrick = $newArr['hattrick'] * $value['hattrick'];
                }

                /*Econ*/
                $econ = $zerotofourecons = $fourtosixecons = $sixplusecons = 0;
                if (!empty($value['overs']) && !empty($value['run']) && $value['overs'] != 0) {
                    $econ = $value['run'] / $value['overs'];
                }

                if (!empty($econ)) {
                    if ((!empty($newArr['0to4econs'])) && ($econ <= 4)) {
                        $zerotofourecons = $newArr['0to4econs'];
                    }
                    //econ = 5
                    if (!empty($newArr['4to6econs']) && ($econ > 4) && ($econ <= 6)) {
                        $fourtosixecons = $newArr['4to6econs'];
                    }
                    if ((!empty($newArr['6plusecons'])) && ($econ > 6)) {
                        $sixplusecons = $newArr['6plusecons'];
                    }
                }

                /*PLayer of the match and match status logic starts here*/
                $addPlayerBounPoint = 0;
                $potm_match_bonus = 0;
                $getPOTMBonus = Team::where('id', $fixtureDetails->team)->value('match_bonus');
                $getTeamPlayerBonus = Team::where('id', $fixtureDetails->team)->first();
                if (!empty($getPOTMBonus) && $request->potm_match_status == 1) {
                    $potm_match_bonus = $getPOTMBonus;
                }
                if (!empty($request->potm)) {
                    if (in_array($value['player_id'], $request->potm)) {
                        $addPlayerBounPoint = $getTeamPlayerBonus->potm_bonus;
                    }
                }
                /*PLayer of the match and match status logic finish here*/
                $fantasyPoints = ($rs + $fours + $sixes + $wks + $mdns + $cs + $cwks + $sts + $rods + $roas + $dks + $zerotofourecons + $fourtosixecons + $sixplusecons + $hattrick + $match + $extraWkcts + $addPlayerBounPoint + $potm_match_bonus);

                $getFeedbackBonus = $this->getFeedbackBonus($value['player_id'], $request->fixture_id);
                $obj->fantasy_points = $fantasyPoints + $getFeedbackBonus;
                $obj->totw = $this->saveTotwPoint($fixture_id, $value['player_id']);

                $obj->potm = !empty($request->potm) ? @serialize($request->potm)  : '';

                $obj->potm_match_status = !empty($request->potm_match_status) ? $request->potm_match_status
                    : 0;
                $obj->fall_of_wickets = !empty($request->fall_of_wickets) ? $request->fall_of_wickets : '';
                $obj->match_report = !empty($request->match_report) ? $request->match_report : '';

                $obj->scorcard_link_name = !empty($request->scorcard_link_name) ? $request->scorcard_link_name : '';
                $obj->scorcard_link_url = !empty($request->scorcard_link_url) ? $request->scorcard_link_url : '';

                /*Multipe scorecards upload option*/
                // if (!empty($request->player_card)) {
                //     $imageName = [];
                //     foreach ($request->player_card as $key => $socrecardValue) {
                //         $extension = $socrecardValue->getClientOriginalExtension();
                //         $newFolder = strtoupper(date('M') . date('Y')) . '/';
                //         $folderPath = PLAYER_CARD_IMAGE_ROOT_PATH . $newFolder;
                //         if (!File::exists($folderPath)) {
                //             File::makeDirectory($folderPath, $mode = 0777, true);
                //         }
                //         $userImageName = time() . $key . '-player-card.' . $extension;
                //         $image = $newFolder . $userImageName;
                //         if ($n == 1) {
                //             if ($socrecardValue->move($folderPath, $userImageName)) {
                //                 $imageName[] = $image;
                //             }
                //         }
                //         $obj->player_card = implode(",", $imageName);
                //     }
                // }

                $obj->save();
                //Save fixture extra info
                $fObj = Fixture::find($fixture_id);
                $fObj->scorcard_link_name = $request->scorcard_link_name;
                $fObj->scorcard_link_url = $request->scorcard_link_url;
                $fObj->fall_of_wickets = $request->fall_of_wickets;
                $fObj->match_report = $request->match_report;
                $fObj->potm_match_status = $request->potm_match_status;
                $fObj->potm = !empty($request->potm) ? @serialize($request->potm)  : '';
                $fObj->save();

                $playerPoints = PlayerPoint::firstOrNew(['score_id' => $value['id']]);
                $playerPoints->fixture_id = $fixture_id;
                $playerPoints->score_id = $value['id'];
                $playerPoints->player_id = $value['player_id'];
                $playerPoints->inning = $value['inning'];
                $playerPoints->fantasy_points = $fantasyPoints;
                $playerPoints->save();
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Scorecards has been updated successfully.";
        return response()->json($data);
    }

    public function getFeedbackBonus($player_id = null, $fixture_id = null)
    {
        $getPointId = FeedbackFixturePoint::where('player_id', $player_id)
            ->where('fixture_id', $fixture_id)
            ->sum('fantasy_point');

        $totalSumPoint = 0;
        if (!empty($getPointId)) {
            $totalSumPoint = $getPointId;
        }

        return $totalSumPoint;
    }
    public function saveTotwPoint($fixture_id, $player_id)
    {
        $getTotwBonus = 0;
        if (!empty($fixture_id) && !empty($player_id)) {
            $getTotwBonus = TeamOfTheWeek::where('player_id', $player_id)->where('fixture_id', $fixture_id)->value('bonus');
            if (empty($getTotwBonus))
                return 0;
        }
        return $getTotwBonus;
    }
    public function showScorecard($fixture_id = null)
    {

        $team_id = null;
        $status = FixtureScorcard::where('fixture_id', $fixture_id)->orderBy('created_at', 'DESC')
            ->value('status');
        $teamPlayerCount = TeamPlayer::where('fixture_id', $fixture_id)->count();
        $fixtureScorecards = FixtureScorcard::where('fixture_id', $fixture_id)->count();
        if ($teamPlayerCount < 5) {
            $needToAdd = (5 - $teamPlayerCount);
            Session::flash('flash_notice', trans("Please add $needToAdd more player to add scorecard."));
            return Redirect::to('admin/fixture');
        }
        if ($fixtureScorecards < 5) {
            FixtureScorcard::where('fixture_id', $fixture_id)->delete();
        }

        $DB = FixtureScorcard::query();
        $searchVariable = array();
        $inputGet = Input::get();
        /* seacrching on the basis of username and email */
        if ((Input::get()) || isset($inputGet['display']) || isset($inputGet['page'])) {
            $searchData = Input::get();
            unset($searchData['display']);
            unset($searchData['_token']);
            if (isset($searchData['order'])) {

                unset($searchData['order']);
            }

            if (isset($searchData['sortBy'])) {

                unset($searchData['sortBy']);
            }
            if (isset($searchData['page'])) {

                unset($searchData['page']);
            }

            $date_from = '';

            $date_to = '';
            foreach ($searchData as $fieldName => $fieldValue) {

                if (!empty($fieldValue) || $fieldValue == 0) {

                    $DB->where("fixture_scorecards.$fieldName", 'like', '%' . $fieldValue . '%');
                }

                $searchVariable = array_merge($searchVariable, array(
                    $fieldName => $fieldValue,
                ));
            }
        }

        $sortBy = (Input::get('sortBy')) ? Input::get('sortBy') : 'fixture_scorecards.created_at';

        $order = (Input::get('order')) ? Input::get('order') : 'DESC';
        $fixtureData = Fixture::where('fixtures.id', $fixture_id)->leftJoin('grades', 'grades.id', '=', 'fixtures.grade')
            ->leftJoin('teams', 'teams.id', '=', 'fixtures.team')
            ->select('fixtures.*', 'grades.grade as grade', 'teams.name as team_name', 'teams.id as team_id')
            ->first();
        $scoreData = FixtureScorcard::where(['fixture_id' => $fixture_id])->count();
        $getTeamPlayer = TeamPlayer::where('fixture_id', $fixture_id)->select('player_id')
            ->get();
        if ($scoreData == 0) {
            if (!$getTeamPlayer->isEmpty()) {
                if ($fixtureData->match_type == ONEDAYMATCH) {
                    foreach ($getTeamPlayer as $key => $value) {

                        $obj = new FixtureScorcard;

                        $obj->player_id = $value->player_id;

                        $obj->fixture_id = $fixture_id;

                        $obj->inning = 1;

                        $obj->save();
                    }
                } else {
                    foreach ($getTeamPlayer as $key => $value) {

                        $obj = new FixtureScorcard;

                        $obj->player_id = $value->player_id;

                        $obj->fixture_id = $fixture_id;

                        $obj->inning = 1;

                        $obj->save();
                    }
                    foreach ($getTeamPlayer as $key => $value) {

                        $obj = new FixtureScorcard;

                        $obj->player_id = $value->player_id;

                        $obj->fixture_id = $fixture_id;

                        $obj->inning = 2;

                        $obj->save();
                    }
                }
            }
        } else {
        }
        $result = $DB->leftjoin('players', 'fixture_scorecards.player_id', '=', 'players.id')
            ->select('fixture_scorecards.*', 'players.full_name as player_name', 'players.position as player_position')

            ->where('fixture_scorecards.fixture_id', $fixture_id)
            ->orderBy($sortBy, $order)
            ->paginate(40);
        $playerScorcards = FixtureScorcard::where('fixture_id', $fixture_id)->where('player_card', '!=', '')
            ->value('player_card');
        $complete_string = Input::query();

        unset($complete_string["sortBy"]);

        unset($complete_string["order"]);

        $query_string = http_build_query($complete_string);

        $result->appends(Input::all())
            ->render();
        $playerImages = FixtureScorcard::where('fixture_id', $fixture_id)->where('player_card', '!=', '')
            ->first();
        return View::make('admin.fixture.show_scorecard', compact('result', 'searchVariable', 'sortBy', 'order', 'query_string', 'fixture_id', 'team_id', 'fixtureData', 'status', 'playerImages', 'playerScorcards'));
    }
}// end ClubController class
