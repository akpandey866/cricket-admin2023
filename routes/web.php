<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
$router->options(
    '/{any:.*}',
    [
        function () {
            return response(['status' => 'success']);
        }
    ]
);

// include app()->basePath('app') . '/global_constants.php';
$router->group(['prefix' => '/'], function () use ($router) {
    $router->post('login',  ['uses' => 'AdminLoginController@login']);
    $router->post('save-bonus-card', 'CommonController@saveBonusCard');
    // Route::any('/login', 'AdminLoginController@login')->name('admin.login');
});


/*
 *
 * AUTHENTICATED ROUTES
 *
 */


$router->group(
    [
        'middleware' => ['jwt.auth'],
    ],
    function ($router) {
        $router->get('/get-club-details', 'ClubController@getClubDetails');
        $router->post('/update-game-admin', 'ClubController@updateGameAdmin');
        $router->post('/update-game-social', 'ClubController@updateGameSocial');
        $router->post('/update-game-intro', 'ClubController@updateGameIntro');
        $router->post('/update-about-game', 'ClubController@updateAboutGame');
        $router->post('/update-basic-setting', 'ClubController@updateBasicSetting');
        $router->post('/update-fee-info', 'ClubController@updateFeeInfo');
        $router->get('/get-state-by-country-id/{countryId}', 'ClubController@getStateByCountryId');

        // Grade Routing
        $router->get('/grades/listing', 'GradeController@index');
        $router->post('/grades/save-grade', 'GradeController@saveGrade');
        $router->post('/grades/delete-grade', 'GradeController@deleteGrade');
        $router->post('/grades/update-grade-point-system', 'GradeController@updateGradePointSystem');
        $router->post('/grades/edit-grade', 'GradeController@editGrade');
        $router->get('/grades/grade-details/{id}', 'GradeController@gradeDetail');
        $router->get('/grades/grade-data', 'GradeController@gradeData');
        $router->get('/grades/grade-point-system/{gradeId}', 'GradeController@gradePointSystem');
        $router->get('/grades/reset-to-default/{gradeId}', 'GradeController@resetToDefault');
        $router->post('/grades/multiply-grade', 'GradeController@multiplyGrade');
        $router->post('/grades/copy-grade', 'GradeController@copyGrade');

        // Bonus Card
        $router->get('/common/bonus-cards', 'CommonController@bonusCard');
        $router->get('/common/club-players', 'CommonController@clubPlayer');
        $router->get('/common/bonus-card-players/{round}', 'CommonController@bonusCardPlayer');
        $router->post('/common/save-bonus-card', 'CommonController@saveBonusCard');
        $router->post('/common/save-bonus-card-player-point', 'CommonController@saveBonusCardPlayerPoint');

        // Branding
        $router->get('/common/branding', 'CommonController@branding');
        $router->post('/common/edit-branding', 'CommonController@editBranding');

        // Game Spot Routing
        $router->get('/common/game-spot', 'CommonController@gameSpot');
        $router->post('/common/update-game-spot', 'CommonController@updateGameSpot');

        // Trades
        $router->post('/common/update-trades', 'CommonController@updateTrades');

        // Trades
        $router->get('/common/c-vcaptain', 'CommonController@CVCaptain');
        $router->get('/common/get-bonus-point', 'CommonController@getBonusPoint');
        $router->post('/common/update-bonus-point', 'CommonController@updateBonusPoint');

        // Bonus Point
        $router->get('/common/c-vcaptain', 'CommonController@CVCaptain');
        // Player Routing
        $router->get('/players/listing', 'PlayerController@index');
        $router->get('/players/player-details/{id}', 'PlayerController@playerDetail');
        $router->get('/players/get-position-team-val-bat-bowl-style', 'PlayerController@getTeamPositionValueBatBowlStyle');
        $router->post('/players/edit-player', 'PlayerController@editPlayer');
        $router->post('/players/save-player', 'PlayerController@savePlayer');
        // Fantasy Values(Player Price) Spot Routing
        $router->get('/players/fantasy-values', 'PlayerController@fantasyValue');
        $router->post('/players/update-fantasy-values', 'PlayerController@updateFantastValue');

        // Player Availability
        $router->get('/player-availabilities/listing', 'PlayerAvailabilityController@index');
        $router->get('/player-availabilities/availability-details/{id}', 'PlayerAvailabilityController@playerDetail');
        $router->post('/player-availabilities/save-availability', 'PlayerAvailabilityController@saveAvailability');
        $router->post('/player-availabilities/edit-availability', 'PlayerAvailabilityController@editAvailability');

        // Team routing start here
        $router->get('/teams/listing', 'TeamController@index');
        $router->get('/teams/team-details/{id}', 'TeamController@TeamDetail');
        $router->post('/teams/save-team', 'TeamController@saveTeam');
        $router->post('/teams/edit-team', 'TeamController@editTeam');
        $router->post('/teams/delete-team', 'TeamController@deleteTeam');
        $router->get('/teams/team-list-by-grade/{grade_id}', 'TeamController@getTeamListByGrade');

        // Fixture Routing
        $router->get('/fixtures/listing', 'FixtureController@index');
        $router->post('/fixtures/save-fixture', 'FixtureController@saveFixture');
        $router->post('/fixtures/delete-fixture', 'FixtureController@deleteFixture');
        $router->post('/fixtures/edit-fixture', 'FixtureController@editFixture');
        $router->get('/fixtures/fixture-details/{id}', 'FixtureController@FixtureDetail');
        $router->get('/fixtures/get-match-list-types', 'FixtureController@getMatchListType');

        // Team Player Routing is here
        Route::get('/team-player/{fixture_id}', array('as' => 'TeamPlayer.index', 'uses' => 'TeamPlayerController@index'));
        Route::get('/getPickedPlayer/{player_id}/{fixture_id}', array('as' => 'TeamPlayer.index', 'uses' => 'TeamPlayerController@index'));
        Route::get('/team-player-listing', array('as' => 'TeamPlayer.listing', 'uses' => 'TeamPlayerController@teamPlayerListing'));
        Route::get('/delete-team-player/{playerId}/{fixtureId}', array('as' => 'TeamPlayer.deleteTeamPlayer', 'uses' => 'TeamPlayerController@deleteTeamPlayer'));
        Route::get('/add-team-player-direct/{playerId}/{fixtureId}', array('as' => 'TeamPlayer.saveTeamPlayerDirect', 'uses' => 'TeamPlayerController@saveTeamPlayerDirect'));
        Route::post('/save-multi-team-player', 'TeamPlayerController@saveMultiTeamPlayer');

        // Scorecard Routing
        Route::get('/scorecard/{fixtureId}', 'ScoreCardController@scorecardDetail');
        Route::post('/save-scorecard', 'ScoreCardController@editFixtureScorcard');

        // Power Control routing
        Route::get('/power-control', 'CommonController@powerControl');
        Route::post('/common/edit-power-control', 'CommonController@editPowerControl');
        Route::post('/common/change-power-control-status', 'CommonController@changePowerControlStatus');

        // Verify User
        Route::get('/common/verify-users', 'CommonController@verifyUsers');
        Route::post('/common/save-verify-user', 'CommonController@saveVerifyUser');


        // Article Routing
        $router->get('/articles/listing', 'ArticleController@index');
        $router->post('/articles/save-article', 'ArticleController@saveArticle');
        $router->post('/articles/delete-article', 'ArticleController@deleteArticle');
        $router->post('/articles/edit-article', 'ArticleController@editArticle');
        Route::get('/articles/article-details/{id}', 'ArticleController@articleDetail');
        $router->get('/articles/article-data', 'ArticleController@articleData');

        // Rounds Routing
        $router->get('/rounds/listing', 'RoundController@index');
        $router->post('/rounds/save-round', 'RoundController@saveRound');
        $router->post('/rounds/delete-round', 'RoundController@deleteRound');
        $router->post('/rounds/edit-round', 'RoundController@editRound');
        $router->get('/rounds/round-details/{id}', 'RoundController@roundDetail');

        // Sponsors Routing
        $router->get('/sponsors/listing', 'SponsorController@index');
        $router->post('/sponsors/save-sponsor', 'SponsorController@saveSponsor');
        $router->post('/sponsors/delete-sponsor', 'SponsorController@deleteSponsor');
        $router->post('/sponsors/edit-sponsor', 'SponsorController@editSponsor');
        $router->get('/sponsors/sponsor-details/{id}', 'SponsorController@sponsorDetail');

        // User Routing
        $router->get('/users/listing', 'UserController@index');
        $router->post('/users/save-user', 'UserController@saveUser');
        $router->post('/users/delete-user', 'UserController@deleteGrade');
        $router->post('/users/edit-user', 'UserController@editUser');
        $router->get('/users/user-details/{id}', 'UserController@userDetail');

        // Sponsors Routing
        $router->get('/game-notifications/listing', 'GameNotificationController@index');
        $router->post('/game-notifications/save-notification', 'GameNotificationController@saveNotification');
        $router->post('/game-notifications/delete-notification', 'GameNotificationController@deleteNotification');
        $router->post('/game-notifications/edit-notification', 'GameNotificationController@editNotification');
        $router->get('/game-notifications/notification-details/{id}', 'GameNotificationController@notificationDetail');

        // Sponsors Routing
        $router->get('/feedback-fantasy/category-listing', 'FeedbackFantasyController@cateogryIndex');
        $router->post('/feedback-fantasy/save-category', 'FeedbackFantasyController@saveCategory');
        $router->post('/feedback-fantasy/delete-category', 'FeedbackFantasyController@deleteCategory');
        $router->get('/feedback-fantasy/category-details/{id}', 'FeedbackFantasyController@categoryDetail');
        $router->get('/feedback-fantasy/managers-listing', 'FeedbackFantasyController@coachListing');
        $router->post('/feedback-fantasy/save-manager', 'FeedbackFantasyController@saveFeeddbackManager');
        $router->post('/feedback-fantasy/delete-manager', 'FeedbackFantasyController@deleteFeedbackManager');
        $router->get('/feedback-fantasy/manage-access-by-team', 'FeedbackFantasyController@manageAccessByTeam');
        $router->get('/feedback-fantasy/manage-access-by-fixtures', 'FeedbackFantasyController@manageAccessByFixture');
        $router->post('/feedback-fantasy/save-manage-access-by-team', 'FeedbackFantasyController@saveManageAcessByTeam');
        $router->post('/feedback-fantasy/save-manage-access-by-fixtures', 'FeedbackFantasyController@saveManageAcessByFixture');
        $router->get('/feedback-fantasy/show-fixture-listing/{id}', 'FeedbackFantasyController@showFixtureListing');
        $router->get('/feedback-fantasy/delete-manager-fixture/{id}/{manager_access_id}', 'FeedbackFantasyController@deleteManagerFixture');
        $router->get('/feedback-fantasy/show-team-listing/{id}', 'FeedbackFantasyController@showTeamListing');
        $router->get('/feedback-fantasy/delete-manager-access/{id}', 'FeedbackFantasyController@deleteManagerAccess');
        $router->get('/feedback-fantasy/delete-manager-team/{id}/{manager_access_id}', 'FeedbackFantasyController@deleteManagerTeam');
        $router->get('/feedback-fantasy/feedback-point-system', 'FeedbackFantasyController@feedbackPointSystem');
        $router->post('/feedback-fantasy/save-feedback-point-system', 'FeedbackFantasyController@saveFeedbackPointSystem');
        $router->get('/feedback-fantasy/display-setting', 'FeedbackFantasyController@displaySetting');
        $router->post('/feedback-fantasy/update-display-setting', 'FeedbackFantasyController@updateDisplaySetting');

        // Fixture Voting routing start here
        $router->get('/fixture-voting/listing', 'FixtureVotingController@index');
        $router->get('/fixture-voting/get-fixture-voting-data/{fixture_id}', 'FixtureVotingController@getFixtureVote');
        $router->get('/fixture-voting/get-average-fixture-voting/{fixture_id}', 'FixtureVotingController@getAverageFitureVote');
        $router->get('/fixture-voting/user-player-rating/{fixture_id}', 'FixtureVotingController@userPlayerRating');
        $router->get('/fixture-voting/user-player-rating-modal/{fixture_id}/{user_id}', 'FixtureVotingController@userPlayerRatingModal');
    }
);

function prd($data = "")
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    die;
}
