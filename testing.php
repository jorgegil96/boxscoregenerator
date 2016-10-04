<?php
include 'simple_html_dom.php';
date_default_timezone_set('America/New_York');

// This URL provides all games from to the given date("yyyy-mm-dd").
$SCOREBOARD_BASE_URL = 'http://sports.yahoo.com/nba/scoreboard/?dateRange=';
$YAHOO_SPORTS_BASE_URL = 'http://sports.yahoo.com';

// Use given date. If none was provided, use today's date.
$date = date("Y-m-d");
if (isset($_GET['date'])) {
	$date = $_GET['date'];
}

$scoreboardHtml = getHtmlFromUrl($SCOREBOARD_BASE_URL.$date);

// Save all links found in the html in an array.
$allLinksInHtml = $scoreboardHtml->find('a');

// Find links of games.
$gameLinks = findGameLinks($allLinksInHtml);

/****Remove***/
$gameLink = $gameLinks[0];

/**Remove**/
$boxScoreHtml = new simple_html_dom();
$boxScoreHtml->load_file($gameLink);

// Full box score.
$gameStats = $boxScoreHtml->find('div[class=match-stats]')[0];
// Html box score of first team.
$teamAStatsHtml = $gameStats->children(0);
// Html box score of second team.
$teamBStatsHtml = $gameStats->children(1);

$teamAStats = parseTeamStats($teamAStatsHtml);
$teamBStats = parseTeamStats($teamBStatsHtml);

echo "************************************************************<br>";
echo "<h1>".$teamAStats[0]."</h1><br>";
printMatrixAsHtmlTable($teamAStats[1]);


echo "************************************************************<br>";
echo "<h1>".$teamBStats[0]."</h1><br>";
printMatrixAsHtmlTable($teamBStats[1]);





//$gameStats = $boxScoreHtml->find('div[class=match-stats]');
//echo $gameStats;

/*
* Returns an HTML document from the given URL.
*/
function getHtmlFromUrl($url) {
	return file_get_html($url);
}

/*
* Finds and returns the links that belong to nba games (boxscores) in a list of 
* all kinds of links. Game links have the format: 
* "/nba/golden-state-warriors-toronto-raptors-2016100128/".
*/
function findGameLinks($links) {
	$gameLinks = [];
	foreach ($links as $link) {
		if (substr($link->href, 0, strlen("/nba/")) === "/nba/") {
			array_push($gameLinks, $GLOBALS['YAHOO_SPORTS_BASE_URL'].$link->href);
		}
	}
	return $gameLinks;
}

/**
* Given an html div containg the box score of a single team, returns an array that
* contains the team name as the first element, and a matrix representing the box
* score as the second element.
*/
function parseTeamStats($teamBoxScore) {
	$startersHeader = array("Starters", "Min", "FG", "3pt", "FT", "+/-", "Off", "Def", "Reb",
	 "Ast", "TO", "Stl", "Blk", "BA", "PF", "Pts");
	$benchHeader = array("Bench", "Min", "FG", "3pt", "FT", "+/-", "Off", "Def", "Reb",
	 "Ast", "TO", "Stl", "Blk", "BA", "PF", "Pts");

	// HEADER SECTION
	$header = $teamBoxScore->children(0);
	$teamName = $header->find('span', 0);

	// STARTERS SECTION
	$startersTable = $teamBoxScore->children(1)->children(0)->children(0);
	// Matrix of all starters and their stats.
	$startersStats = parsePlayersTable($startersTable);

	// BENCH SECTION
	$benchTable = $teamBoxScore->children(2)->children(0)->children(0);
	// Matrix of all bench and their stats. Last row contains team totals.
	$benchStats = parseBenchTable($benchTable);

	// Complete matrix of ALL players and totals of this team.
	$boxScore = [];
	array_push($boxScore, $startersHeader);
	foreach ($startersStats as $player) {
		array_push($boxScore, $player);
	}
	array_push($boxScore, $benchHeader);
	foreach ($benchStats as $player) {
		array_push($boxScore, $player);
	}

	// Array containing a team's name and box score.
	$teamStats = [];
	array_push($teamStats, $teamName);
	array_push($teamStats, $boxScore);

	return $teamStats;
}

/*
* Returns a matrix of players and their stats.
*/
function parsePlayersTable($table) {
	$playersStats = [];

	$startersBody = $table->children(1);
	foreach($startersBody->find('tr') as $row) {
		array_push($playersStats, parsePlayerRow($row));
	}

	return $playersStats;
}

/*
* Returns a matrix of bench players and their stats. Additionally, this table
* contains two extra rows with totals and percentages.
*/
function parseBenchTable($table) {
	$playersStats = [];

	$startersBody = $table->children(1)->find('tr');
	$numPlayers = count($startersBody) - 2;
	$rowIndex = 0;
	foreach($startersBody as $row) {
		if ($rowIndex < $numPlayers) {
			// Parse row as player for all players.
			array_push($playersStats, parsePlayerRow($row));
		} else {
			// Parse 2nd from last row as total.
			if ($rowIndex == $numPlayers) {
				array_push($playersStats, parseTotalsRow($row));
			}
			// Ignore last row.
		}
		$rowIndex++;
	}

	return $playersStats;
}

/*
* Returns an array containing a player name and his stats given an html row.
*/
function parsePlayerRow($row) {
	$playerStats = [];
	$playerName = $row->children(0)->children(0)->children(0)->innertext;
	array_push($playerStats, $playerName);

	$stats = $row->find('td');
	foreach ($stats as $stat) {
		array_push($playerStats, $stat->innertext);
	}

	return $playerStats;
}

/*
* Returns an array containg the teams total stats given an html row.
*/
function parseTotalsRow($row) {
	$totalStats = [];
	array_push($totalStats, "Totals");

	$stats = $row->find('td');
	foreach ($stats as $stat) {
		array_push($totalStats, $stat->innertext);
	}

	return $totalStats;
}

function printMatrix($matrix) {
	foreach ($matrix as $row) {
		foreach ($row as $col) {
			echo $col." | ";
		}
		echo "<br>";
	}
}

function printMatrixAsHtmlTable($matrix) {
?>
	<?php if (count($matrix) > 0): ?>
<table>
  <tbody>
<?php $cont = 0; ?>
<?php foreach ($matrix as $row): array_map('htmlentities', $row); ?>
<?php if ($cont == 0 || $cont == 6) { ?>
    <tr>
      <td><?php echo "<b>".implode('</td><td>', $row)."</b>"; ?></td>
    </tr>
<?php } else { ?>
	<tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php } ?>
<?php $cont++; ?>
<?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php
}



?>




















