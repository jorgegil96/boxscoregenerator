<?php
include 'simple_html_dom.php';
date_default_timezone_set('America/Los_Angeles');

// This URL provides all games from to the given date("yyyymmdd").
$SCOREBOARD_BASE_URL = 'http://msnbchosted.stats.com/nba/scoreboard.asp?seasontype=pre&day=';
$NBC_SPORTS_SCORES_BASE_URL = 'http://scores.nbcsports.com';

// Use today's date.
$date = date("Ymd");
//$date = "20161019";

// Html with all games in it.
$scoreboardHtml = getHtmlFromUrl($SCOREBOARD_BASE_URL.$date);

$boxscores = [];
$matchups = getGameMatchups($scoreboardHtml->find('a'));
foreach($matchups as $game) {
	if ($game[2] != null) {
		array_push($boxscores, getBoxscore(getHtmlFromUrl($game[2])));
	}
}
saveBoxScoreJson($boxscores);

/*
* Returns an HTML document from the given URL.
*/
function getHtmlFromUrl($url) {
	return file_get_html($url);
}

/*
* Finds and returns a 2d array containing two teams and a boxcores link for each game.
*/
function getGameMatchups($links) {
	$matchups = [];
	$gameCount = 0;
	$teamCount = 0;
	foreach($links as $link) {
		if ($link->innertext == "Box") {
			$matchups[$gameCount - 1][2] = $GLOBALS['NBC_SPORTS_SCORES_BASE_URL'].substr($link->href, 0, 37);
		}

		if (substr($link->href, 0, strlen("/nba/teamstats.asp")) === "/nba/teamstats.asp") {
			$matchups[$gameCount][$teamCount] = $link->innertext;
			$teamCount++;
		}
		
		if ($teamCount == 2) {
			$gameCount++;
			$teamCount = 0;
		}
	}
	return $matchups;
}

function getBoxscore($html) {
	$htmlBoxscore = $html->find('div[id=shsBoxscore]', 0);
	$tables = $htmlBoxscore->find('table[class=shsBorderTable]');
	if (sizeof($tables) == 2) {
		$htmlTeamABoxscore = $tables[0];
		$htmlTeamBBoxscore = $tables[1];
	}else if(sizeof($tables) == 3) {
		$htmlTeamABoxscore = $tables[1];
		$htmlTeamBBoxscore = $tables[2];
	}

	$teamABoxscore = parseHtmlBoxscore($htmlTeamABoxscore);
	$teamBBoxscore = parseHtmlBoxscore($htmlTeamBBoxscore);
	
	return [$teamABoxscore, $teamBBoxscore];
}

/**
* Given an html div containg the box score of a single team, returns an array that
* contains the team name as the first element, and a matrix representing the box
* score as the second element.
*/
function parseHtmlBoxscore($htmlTeamBoxScore) {
	$teamName = $htmlTeamBoxScore->children(0)->children(0)->innertext;

	// Matrix of a boxscore.
	$box = [];

	// Add row of headers to matrix.
	$box[0] = [];
	foreach($htmlTeamBoxScore->find('tr[class=shsColTtlRow]', 0)->find('td') as
		$headerElem) {
		array_push($box[0], $headerElem->innertext);
	}

	// Add a rows with stats for each player.
	$htmlPlayerRows = getPlayerRows($htmlTeamBoxScore);
	foreach($htmlPlayerRows as $row) {
		array_push($box, getPlayerArray($row));
	}

	// Add a row for team total stats.
	$totalsRow = [];
	$tr = $htmlTeamBoxScore->find('tr[class=shsColTtlRow]', 1);
	foreach($tr->find('td') as
		$totalElem) {
		array_push($totalsRow, str_replace("<br>", "", $totalElem->innertext));
	} 
	array_push($box, $totalsRow);

	return [$teamName, $box];
}

/**
* Returns an array of html rows, each containing stats of a player.
*/
function getPlayerRows($htmlTable) {
	$rowsCero = $htmlTable->find('tr[class=shsRow0Row]');
	$rowsOne = $htmlTable->find('tr[class=shsRow1Row]');

	return entwine($rowsCero, $rowsOne);
}

/**
* Combines 2 arrays by weaving all elements.
* E.g. given arr1 = [A, B, C] and arr2 = [D, E, F] returns [A, D, B, E, C, F].
*/
function entwine($arr1, $arr2) {
	$result = [];
	$len1 = sizeof($arr1);
	$len2 = sizeof($arr2);

	$count1 = 0;
	$count2 = 0;
	for ($i = 0; $i < $len1 + $len2 - 1; $i++) {
		if ($i % 2 == 0) {
			array_push($result, $arr1[$count1]);
			$count1++;
		} else {
			array_push($result, $arr2[$count2]);
			$count2++;
		}
	}

	while ($count1 < $len1) {
		array_push($result, $arr1[$count1]);
		$count1++;
	}
	while ($count2 < $len2) {
		array_push($result, $arr2[$count2]);
		$count2++;
	}
	return $result;
}

/**
* Returns an array containg player stats parsed from an HTML row.
*/
function getPlayerArray($htmlRow) {
	$arr = [];
	foreach($htmlRow->find('td') as $col) {
		if ($col->find('a', 0) != null) {
			array_push($arr, $col->find('a', 0)->innertext);
		} else {
			array_push($arr, $col->innertext);
		}
	}
	return $arr;
}

function printMatrix($matrix) {
	foreach ($matrix as $row) {
		foreach ($row as $col) {
			echo $col." | ";
		}
		echo "<br>";
	}
}

function saveBoxScoreJson($boxscores) {
	$id = 0;
	$gamesJson = [];
	foreach($boxscores as $boxscore) {
		$temp = [];

		$visitorBox = $boxscore[0];
		$homeBox = $boxscore[1];

		$temp['id'] = $id;
		$temp['home'] = $homeBox[0];
		$temp['home_score'] = $homeBox[1][sizeof($homeBox[1]) - 1][14];
		$temp['home_boxscore'] = $homeBox[1];
		$temp['visitor'] = $visitorBox[0];
		$temp['visitor_score'] = $visitorBox[1][sizeof($visitorBox[1]) - 1][14];
		$temp['visitor_boxscore'] = $visitorBox[1];

		array_push($gamesJson, $temp);

		$id++;
	}
	$result = [];
	$result['games'] = $gamesJson;

	echo json_encode($result);

	$fp = fopen('api/v1/games.json', 'w');
	fwrite($fp, json_encode($result));
	fclose($fp);
}

?>






















