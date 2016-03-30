<?php

include 'simple_html_dom.php';

// Text to be printed onto the TextArea
$textToReddit = "";

// GET DATE FROM URL. IF EMPTY, CHOOSE TODAY.
$today = date("Ymd");
$dateChosen = false;
if (isset($_GET['date'])) {
	$date = $_GET['date'];
	$dateChosen = true;
}
else {
	$date = dash($today);
}

// GET GAME ID FROM URL
$gameID = $_GET['gameID'];

// GAMES DATA TO GET TODAY'S GAMES AND IDs
$gamesData = json_decode(file_get_contents('http://data.nba.com/data/1h/json/cms/noseason/scoreboard/'.noDash($date).'/games.json'));
$gamesData = $gamesData->sports_content->games;
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>NBA Box Score Generator</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<link rel="stylesheet" href="https://cdn.materialdesignicons.com/1.5.54/css/materialdesignicons.min.css">


	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>


	<style type="text/css">
		.mdi::before {
		    font-size: 24px;
		    line-height: 14px;
		}
		.btn .mdi::before {
		    position: relative;
		    top: 4px;
		}
		.btn-xs .mdi::before {
		    font-size: 18px;
		    top: 3px;
		}
		.btn-sm .mdi::before {
		    font-size: 18px;
		    top: 3px;
		}
		.dropdown-menu .mdi {
		    width: 18px;
		}
		.dropdown-menu .mdi::before {
		    position: relative;
		    top: 4px;
		    left: -8px;
		}
		.nav .mdi::before {
		    position: relative;
		    top: 4px;
		}
		.navbar .navbar-toggle .mdi::before {
		    position: relative;
		    top: 4px;
		    color: #FFF;
		}
		.breadcrumb .mdi::before {
		    position: relative;
		    top: 4px;
		}
		.breadcrumb a:hover {
		    text-decoration: none;
		}
		.breadcrumb a:hover span {
		    text-decoration: underline;
		}
		.alert .mdi::before {
		    position: relative;
		    top: 4px;
		    margin-right: 2px;
		}
		.input-group-addon .mdi::before {
		    position: relative;
		    top: 3px;
		}
		.navbar-brand .mdi::before {
		    position: relative;
		    top: 2px;
		    margin-right: 2px;
		}
		.list-group-item .mdi::before {
		    position: relative;
		    top: 3px;
		    left: -3px
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="row">
			<h1 style="text-align: center; padding-bottom: 10px;">NBA Box Score Generator for Reddit</h1>
			<div class="col-md-3 col-md-offset-2">

				<!-- FORM TO CHOOSE DATE -->
				<form action="" method="GET">
					<div class="row">
						<div class="col-md-10" style="padding-right: 5px">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1">Date</span>
								<input type="date" class="form-control" style="max-width: 170px;" name="date" value="<?php echo $date; ?>">
							</div>
						</div>
						<div class="col-md-1" style="padding-left: 5px">
							<input type="submit" value="Enter" class="btn btn-primary">
						</div>
					</div>
				</form>

<?php
// SHOW GAMES ONLY AFTER SUBMITTING PREVIOUS FORM
if ($dateChosen) {
?>
				<!-- Form to choose match-up -->
				<form action="" method="GET">
					<div class="row" style="margin-top: 15px">
						<div class="col-md-8">
							<select name="gameID">
							<?
							foreach ($gamesData->game as $game) {
								$matchup = $game->visitor->team_key." @ ".$game->home->team_key;
								echo "<option value='".$game->visitor->team_key.$game->home->team_key."'>".$matchup."</option>";
							}
							?>
							</select>
							<input type="hidden" name="date" value="<?php echo $date; ?>"></input>
							<input type="submit" value="Go!" class="btn btn-primary">
						</div>
					</div>
				</form>
				<p>*Now with LIVE results!</p>

			</div>
			<div class="col-md-6 col-md-offset-1">
				<p>Made for <a href="http://reddit.com/r/nba">/r/NBA</a> by <a href="http://reddit.com/user/jorgegil96">/u/jorgegil96.</a><br>
				Thanks to <a href="http://reddit.com/user/imeanYOLOright">/u/imeanYOLOright</a> for his original Excel design
				and to the creator of <a href="https://github.com/seemethere/nba_py">NBA_PY</a> and its incredible documentation of the NBA API.</p>
				<br>
				Report any issues on <a href="https://github.com/jorgegil96/boxscoregenerator">Github <i class="mdi mdi-github-circle"></i></a> or send me a <a href="http://reddit.com/user/jorgegil96">PM</a>.
			</div>
		</div> <!-- End row -->
		<hr>

<?
// Get HTML from NBA.com
$html = file_get_html('http://www.nba.com/games/'.noDash($date).'/'.$gameID.'/gameinfo.html');

// Navigate DOM to box scores tables
$teamA = $html->find("#nbaGIboxscore", 0)->children(2); // Team A table
$teamB = $html->find("#nbaGIboxscore", 0)->children(3); // Team B table

// Form 2D Array with box score data for each team
$teamABox = getBoxScore($teamA); 
$teamBBox = getBoxScore($teamB);

$awayScore = $teamABox[count($teamABox) - 2][16];
$homeScore = $teamBBox[count($teamBBox) - 2][16];;

}

//printBoxScore($teamABox);
//printBoxScore($teamBBox);

/*
 * Function getBoxScore
 *
 * This function forms a 2D array containing box score data.
 * 1 Row for each player (> 7 and <= 15) plus one for team totals.
 * Columns are: Name, POS, MIN, FGM-A, 3PM-A, FTM-A, +/-, OFF, DEF, TOT
 * AST, PF, ST, TO, BS, BA, PTS except for when a player doesn't play,
 * in that case columns while be: Name and Comment.
 *
 * @param (DOM object)
 * @return (array)
*/
function getBoxScore($teamData) {
	$teamArray = array();
	$i = 0;
	$rowCount = count($teamData->find('tr'));
	foreach ($teamData->find('tr') as $row) {
		// Ignore first 3 rows
		if ($i >= 3) {
			$teamArray[$i - 3] = array();
			$j = 0;
			for ($j = 0; $j < 17; $j++) {
				$col = $row->find('td', $j);
				if ($col != "") {
					if ($j == 0 && $i != $rowCount - 2) {
						$teamArray[$i - 3][$j] = $col->find('a', 0)->innertext;
					} else {
						$teamArray[$i - 3][$j] = $col->innertext;
					}
				} else {
					$teamArray[$i - 3][$j] = "-";
				}
			}
		}
		$i++;
	}
	return $teamArray;
}

/*
 * Function printBoxScore
 *
 * This function prints a 2D array containing a team's box score
 * data.
 *
 * @param (array)
*/
function printBoxScore($teamArray) {
	$lenI = count($teamArray);
	for ($i = 0; $i < $lenI; $i++) {
		$lenJ = count($teamArray[$i]);
		for ($j = 0; $j < $lenJ; $j++) { 
			echo $teamArray[$i][$j]." | ";
		}
		echo $i."<br>";
	}
}

?>


<?php
// SHOW BOXSCORE AND TEXTAREA ONLY AFTER SUBMITTING PREVIOUS FORM
if (isset($_GET['gameID'])) {
	foreach ($gamesData->game as $game) {
		if (substr($game->game_url, 9) == $gameID) {
			$teamAwayName = $game->visitor->nickname;
			$teamAwayShort = $game->visitor->team_key;
			$teamHomeName = $game->home->nickname;
			$teamHomeShort = $game->home->team_key;
			break;
		}
	}

	$textToReddit .= "|
|

||
|:-:|
|[](/".$teamAwayShort.") **".$awayScore." - ".$homeScore."** [](/".$teamHomeShort.")|
|**Box Score: [NBA](http://www.nba.com/games/".noDash($date)."/".$teamAwayShort.$teamHomeShort."/gameinfo.html#nbaGIboxscore)**|";


	$textToReddit .= printTable($teamAwayName, $teamAwayShort, $teamHomeName, $teamHomeShort, $teamABox, $teamBBox);

$textToReddit .= "
||
|:-:|
|^Generator: [^Excel](https://drive.google.com/file/d/0B81kEjcFfuavUmUyUk5OLVAtYzg/view?usp=sharing) ^by ^imeanYOLOright  ^&  ^Web(nbaboxscoregenerator ^.tk) ^by ^jorgegil96|";

}


function printTable($teamAwayName, $teamAwayShort, $teamHomeName, $teamHomeShort, $teamABox2, $teamBBox2) {

	$textToReddit .= "

||||||||||||||||
|:---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|";

	$textToReddit .= "
**[](/".$teamAwayShort.") ".$teamAwayName."**|**MIN**|**FGM-A**|**3PM-A**|**FTM-A**|**+/-**|**ORB**|**DRB**|**REB**|**AST**|**PF**|**STL**|**TO**|**BLK**|**PTS**|
";
?>
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<table class="table table-hover" style='text-align: center'>
			<thead>
				<tr style='font-weight: bold'>
					<th style='text-align: left'><?php echo $teamAwayName; ?></th>
					<th>MIN</th>
					<th>FGM-A</th>
					<th>3PM-A</th>
					<th>FTM-A</th>
					<th>+/-</th>
					<th>OREB</th>
					<th>DREB</th>
					<th>REB</th>
					<th>AST</th>
					<th>PF</th>
					<th>STL</th>
					<th>TOV</th>
					<th>BS</th>
					<th>PTS</th>
				</tr>
			</thead>
			<tbody>
<?php
	$lenI = count($teamABox2);
	for ($i = 0; $i < $lenI - 1; $i++) {
		$lenJ = count($teamABox2[$i]);
?>
				<tr>
<?php
		for ($j = 0; $j < $lenJ; $j++) {

			if ($j != 1 && $j != 13) {
				if ($j == 0) {
					echo "<td style='text-align: left'>".$teamABox2[$i][$j]."</td>";
				} else {
					echo "<td>".$teamABox2[$i][$j]."</td>";
				}

				$textToReddit .= $teamABox2[$i][$j]."|";
			}
		}
		$textToReddit .= "\n";
?>
				</tr>
<?php
	}
?>
			</tbody>
		</table>
<?php

	$textToReddit .= "
||||||||||||||||
|:---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|";

	$textToReddit .= "
**[](/".$teamHomeShort.") ".$teamHomeName."**|**MIN**|**FGM-A**|**3PM-A**|**FTM-A**|**+/-**|**ORB**|**DRB**|**REB**|**AST**|**PF**|**STL**|**TO**|**BLK**|**PTS**|
";

?>
		<table class="table table-hover" style='text-align: center'>
			<thead>
				<tr style='font-weight: bold'>
					<th style='text-align: left'><?php echo $teamHomeName; ?></th>
					<th>MIN</th>
					<th>FGM-A</th>
					<th>3PM-A</th>
					<th>FTM-A</th>
					<th>+/-</th>
					<th>OREB</th>
					<th>DREB</th>
					<th>REB</th>
					<th>AST</th>
					<th>PF</th>
					<th>STL</th>
					<th>TOV</th>
					<th>BS</th>
					<th>PTS</th>
				</tr>
			</thead>
			<tbody>
<?php
	$lenI = count($teamBBox2);
	for ($i = 0; $i < $lenI - 1; $i++) {
		$lenJ = count($teamBBox2[$i]);
?>
				<tr>
<?php
		for ($j = 0; $j < $lenJ; $j++) {

			if ($j != 1 && $j != 13) {
				if ($j == 0) {
					echo "<td style='text-align: left'>".$teamBBox2[$i][$j]."</td>";
				} else {
					echo "<td>".$teamBBox2[$i][$j]."</td>";
				}

				$textToReddit .= $teamBBox2[$i][$j]."|";
			}
		}
		$textToReddit .= "\n";
?>
				</tr>
<?php
	}
?>
			</tbody>
		</table>
	</div>
</div>
<hr>
<?php
	return $textToReddit;
}



function short($player) {
	return substr($player, 0, 1).". ".strstr($player, " ");
}

function noDash($date) {
	return str_replace("-", "", $date);
}

function dash($date) {
	return substr($date, 0, 4)."-".substr($date, 4, 2)."-".substr($date, 6, 2);
}

?>

		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<h2>Copy text below to Reddit</h2>
				<p>Use Ctrl-A</p>
				<textarea cols="130" rows="50"><?php echo $textToReddit; ?></textarea>
			</div>
		</div>
	</div>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-75603173-1', 'auto');
	  ga('send', 'pageview');

	</script>
</body>

</html>























