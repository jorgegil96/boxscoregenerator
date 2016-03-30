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


<?php

// TEXT TO BE PRINTED ONTO THE TEXTAREA
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
?>
<body>
<div class="container">
	<div class="row">
			<h1 style="text-align: center; padding-bottom: 10px;">NBA Box Score Generator for Reddit</h1>
			<div class="col-md-3 col-md-offset-2">





<?


// GAMES DATA TO GET TODAY'S GAMES AND IDs
$gamesData = json_decode(file_get_contents('http://data.nba.com/data/1h/json/cms/noseason/scoreboard/'.noDash($date).'/games.json'));
$gamesData = $gamesData->sports_content->games;

?>

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

<?

// SHOW GAMES ONLY AFTER SUBMITTING PREVIOUS FORM
if ($dateChosen) {
?>
	<form action="" method="GET">
		<div class="row" style="margin-top: 15px">
			<div class="col-md-8">
				<select name="gameID">
				<?
				foreach ($gamesData->game as $game) {
					$matchup = $game->visitor->team_key." @ ".$game->home->team_key."<br>";
					echo "<option value='".$game->id."'>".$matchup."</option>";
				}
				?>
				</select>
				<input type="hidden" name="date" value="<?php echo $date; ?>"></input>
				<input type="submit" value="Go!" class="btn btn-primary">
			</div>
		</div>
	</form>
	<p>*Results will appear when NBA.com uploads the final box score, usually 5-10 minutes after the game.</p>
<?
}
?>
	</div> <!--  end col-md-3 -->
	<div class="col-md-6 col-md-offset-1">
		<p>Made for <a href="http://reddit.com/r/nba">/r/NBA</a> by <a href="http://reddit.com/user/jorgegil96">/u/jorgegil96.</a><br>
		Thanks to <a href="http://reddit.com/user/imeanYOLOright">/u/imeanYOLOright</a> for his original Excel design
		and to the creator of <a href="https://github.com/seemethere/nba_py">NBA_PY</a> and its incredible documentation of the NBA API.</p>
		<br>
		Report any issues on <a href="https://github.com/jorgegil96/boxscoregenerator">Github <i class="mdi mdi-github-circle"></i></a> or send me a <a href="http://reddit.com/user/jorgegil96">PM</a>.
	</div>
</div>
<hr>


<?

// SHOW BOXSCORE AND TEXTAREA ONLY AFTER SUBMITTING PREVIOUS FORM
if (isset($_GET['gameID'])) {

	// GET BOXSCORE DATA USING GAME ID
	// TODO: see what's up with other season types
	$boxscoreData = json_decode(file_get_contents('http://stats.nba.com/stats/boxscoretraditionalv2?EndPeriod=10&EndRange=28800&GameID='.$gameID.'&RangeType=0&Season=2015-16&SeasonType=Regular+Season&StartPeriod=1&StartRange=0'));

	// MOVE TO TEAM STATS
	$teamData = $boxscoreData->resultSets[1]->rowSet;

	$team2A = $teamData[0][2];
	$abbrev2A = $teamData[0][3];
	$team2B = $teamData[1][2];
	$abbrev2B = $teamData[1][3];


	$nameA = $teamData[0][2];
	$PTSA = $teamData[0][23];
	$FGMA = $teamData[0][6];
	$FGAA = $teamData[0][7];
	$FG_PCTA = $teamData[0][8];
	$FG3MA = $teamData[0][9];
	$FG3AA = $teamData[0][10];
	$FG3_PCTA = $teamData[0][11];
	$FTMA = $teamData[0][12];
	$FTAA = $teamData[0][13];
	$FT_PCTA = $teamData[0][14];
	$REBA = $teamData[0][17];
	$ASTA = $teamData[0][18];
	$STLA = $teamData[0][19];
	$BLKA = $teamData[0][20];
	$TOA = $teamData[0][21];
	$PFA = $teamData[0][22];

	$nameB = $teamData[1][2];
	$PTSB = $teamData[1][23];
	$FGMB = $teamData[1][6];
	$FGAB = $teamData[1][7];
	$FG_PCTB = $teamData[1][8];
	$FG3MB = $teamData[1][9];
	$FG3AB = $teamData[1][10];
	$FG3_PCTB = $teamData[1][11];
	$FTMB = $teamData[1][12];
	$FTAB = $teamData[1][13];
	$FT_PCTB = $teamData[1][14];
	$REBB = $teamData[1][17];
	$ASTB = $teamData[1][18];
	$STLB = $teamData[1][19];
	$BLKB = $teamData[1][20];
	$TOB = $teamData[1][21];
	$PFB = $teamData[1][22];

	// MOVE TO PLAYER STATS
	$boxscoreData = $boxscoreData->resultSets[0]->rowSet;

	// 0 MEANS GAME HASN'T ENDED
	if(count($boxscoreData) == 0) {
		$bsReady = false;
	} else {
		$bsReady = true;
	}

	// IF GAME HAS ENDED SHOW BOXSCORE AND TEXTAREA
	if ($bsReady) {

		// GET TEAMS NAME AND ABBREVIATION
		$teamA = $boxscoreData[0][3];
		$abbrevA = $boxscoreData[0][2];
		$teamB = $boxscoreData[count($boxscoreData) - 1][3];
		$abbrevB = $boxscoreData[count($boxscoreData) - 1][2];

		$textToReddit .= "|
|

||
|:-:|
|[](/".$abbrev2A.") **".$PTSA." - ".$PTSB."** [](/".$abbrev2B.")|
|**Box Score: [NBA](http://www.nba.com/games/".noDash($date)."/".$abbrevA.$abbrevB."/gameinfo.html#nbaGIboxscore)**|
|&nbsp;|
";

		$textToReddit .= "
||
|:-:|
|&nbsp;|
|**TEAM STATS**|

|||||||||||||||
|:-|:-:|-:|:-|-:|:-|-:|:-|:-:|:-:|:-:|:-:|:-:|:-:|
|**Teams**|**PTS**|**FG**|**%**|**3P**|**%**|**FT**|**%**|**REB**|**AST**|**STL**|**BLK**|**TO**|**PF**|
";

		$textToReddit .= "|".$nameA."|".$PTSA."|".$FGMA."-".$FGAA."|".$FG_PCTA."|".$FG3MA."-".$FG3AA."|".$FG3_PCTA."|".$FTMA."-".$FTAA."|".$FT_PCTA."|".$REBA."|".$ASTA."|".$STLA."|".$BLKA."|".$TOA."|".$PFA."|
";

		$textToReddit .= "|".$nameB."|".$PTSB."|".$FGMB."-".$FGAB."|".$FG_PCTB."|".$FG3MB."-".$FG3AB."|".$FG3_PCTB."|".$FTMB."-".$FTAB."|".$FT_PCTB."|".$REBB."|".$ASTB."|".$STLB."|".$BLKB."|".$TOB."|".$PFB."|
";

		$textToReddit .= 
"
||
|:-:|
|&nbsp;|
|**INDIVIDUAL PLAYER STATS**|";
$textToReddit .= "

||||||||||||||||
|:---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|";

		// USED TO CHANGE TEAMS MID-TABLE BELOW
		$done = false;


		$textToReddit .= "
**[](/".$abbrevA.") ".$teamA."**|**MIN**|**FGM-A**|**3PM-A**|**FTM-A**|**ORB**|**DRB**|**REB**|**AST**|**STL**|**BLK**|**TO**|**PF**|**+/-**|**PTS**|
";

		// CREATE TABLE
		?>
		<div class="row">
		<div class="col-md-10 col-md-offset-1">
		<table class="table table-hover" style='text-align: center'>
			<thead>
				<tr style='font-weight: bold'>
					<th style='text-align: left'><?php echo $teamA; ?></th>
					<th>MIN</th>
					<th>FGM-A</th>
					<th>3PM-A</th>
					<th>FTM-A</th>
					<th>OREB</th>
					<th>DREB</th>
					<th>REB</th>
					<th>AST</th>
					<th>STL</th>
					<th>BLK</th>
					<th>TO</th>
					<th>PF</th>
					<th>+/-</th>
					<th>PTS</th>
				</tr>
			</thead>
		<?
		foreach ($boxscoreData as $playerData) {
			$city = $playerData[3];
			$player = short($playerData[5]);
			$MIN = $playerData[8];
			$FGM = $playerData[9];
			$FGA = $playerData[10];
			$FG_PCT = $playerData[11];
			$FG3M = $playerData[12];
			$FG3A = $playerData[13];
			$FG3_PCT = $playerData[14];
			$FTM = $playerData[15];
			$FTA = $playerData[16];
			$FT_PCT = $playerData[17];
			$OREB = $playerData[18];
			$DREB = $playerData[19];
			$REB = $playerData[20];
			$AST = $playerData[21];
			$STL = $playerData[22];
			$BLK = $playerData[23];
			$TOV = $playerData[24];
			$PF = $playerData[25];
			$PTS = $playerData[26];
			$PM = $playerData[27];

			// SHOW ROW WITH NEW TEAM ONLY ONCE
			if ($city == $teamB && !$done) {
				?>
				<thead>
				<tr style='font-weight: bold'>
					<th style='text-align: left'><?php echo $teamB; ?></th>
					<th>MIN</th>
					<th>FGM-A</th>
					<th>3PM-A</th>
					<th>FTM-A</th>
					<th>OREB</th>
					<th>DREB</th>
					<th>REB</th>
					<th>AST</th>
					<th>STL</th>
					<th>BLK</th>
					<th>TO</th>
					<th>PF</th>
					<th>+/-</th>
					<th>PTS</th>
				</tr>
				</thead>
				<?

				$textToReddit .= "**[](/".$abbrevB.") ".$teamB."**|**MIN**|**FGM-A**|**3PM-A**|**FTM-A**|**ORB**|**DRB**|**REB**|**AST**|**STL**|**BLK**|**TO**|**PF**|**+/-**|**PTS**|
";

				$done = true;
			}

			echo "<tr>";
			echo "<td style='text-align: left'>".$player."</td>";
			echo "<td>".$MIN."</td>";
			echo "<td>".$FGM."-".$FGA."</td>";
			echo "<td>".$FG3M."-".$FG3A."</td>";
			echo "<td>".$FTM."-".$FTA."</td>";
			echo "<td>".$OREB."</td>";
			echo "<td>".$DREB."</td>";
			echo "<td>".$REB."</td>";
			echo "<td>".$AST."</td>";
			echo "<td>".$STL."</td>";
			echo "<td>".$BLK."</td>";
			echo "<td>".$TOV."</td>";
			echo "<td>".$PF."</td>";
			echo "<td>".$PM."</td>";
			echo "<td>".$PTS."</td>";
			echo "</tr>";

			$textToReddit .= $player."|".$MIN."|".$FGM."-".$FGA."|".$FG3M."-".$FG3A."|".$FTM."-".$FTA."|".$OREB."|".$DREB."|".$REB."|".$AST."|".$STL."|".$BLK."|".$TOV."|".$PF."|".$PM."|".$PTS."|";
			$textToReddit .= "\n";
			
		}

		$textToReddit .= "
||
|:-:|
|^Generator: [^Excel](https://drive.google.com/file/d/0B81kEjcFfuavUmUyUk5OLVAtYzg/view?usp=sharing) ^by ^imeanYOLOright  ^&  ^Web ^by ^jorgegil96|";
		?>
		</table>
		</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<h2>Copy text below to Reddit</h2>
				<p>Use Ctrl-A</p>
				<textarea cols="130" rows="50"><?php echo $textToReddit; ?></textarea>
			</div>
		</div>
		<?
	} else {
		echo "<h3> Boxscore not ready, wait for the game to end.</h3>";
	}
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






































