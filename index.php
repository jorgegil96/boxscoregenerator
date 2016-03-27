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
	$date = $today;
}

// GET GAME ID FROM URL
$gameID = $_GET['gameID'];


echo "<h1>NBA Boxscore Generator for Reddit</h1>";
echo "Enter date (YYYY/MM/DD)<br>";

// GAMES DATA TO GET TODAY'S GAMES AND IDs
$gamesData = json_decode(file_get_contents('http://data.nba.com/data/1h/json/cms/noseason/scoreboard/'.$date.'/games.json'));
$gamesData = $gamesData->sports_content->games;

?>

<!-- FORM TO CHOOSE DATE -->
<form action="" method="GET">
	<input type="text" name="date" value="<?php echo $date; ?>">
	<input type="submit" value="Enter">
</form>

<?

// SHOW GAMES ONLY AFTER SUBMITTING PREVIOUS FORM
if ($dateChosen) {
?>
	<form action="" method="GET">
		<select name="gameID">
		<?
		foreach ($gamesData->game as $game) {
			$matchup = $game->visitor->team_key." @ ".$game->home->team_key."<br>";
			echo "<option value='".$game->id."'>".$matchup."</option>";
		}
		?>
		</select>
		<input type="hidden" name="date" value="<?php echo $date; ?>"></input>
		<input type="submit" value="Go!">
	</form>
<?
}
?>
<hr>

<?

// SHOW BOXSCORE AND TEXTAREA ONLY AFTER SUBMITTING PREVIOUS FORM
if (isset($_GET['gameID'])) {

	// GET BOXSCORE DATA USING GAME ID
	// TODO: see what's up with other season types
	$boxscoreData = json_decode(file_get_contents('http://stats.nba.com/stats/boxscoretraditionalv2?EndPeriod=10&EndRange=28800&GameID='.$gameID.'&RangeType=0&Season=2015-16&SeasonType=Regular+Season&StartPeriod=1&StartRange=0'));

	// MOVE TO TEAM STATS
	$teamData = $boxscoreData->resultSets[1]->rowSet;
	$PTSA = $teamData[0][23];
	$PTSB = $teamData[1][23];

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
|[](/".$abbrevA.") **".$PTSA." - ".$PTSB."** [](/".$abbrevB.")|
|**Box Score: [NBA](http://www.nba.com/games/".$date."/".$abbrevA.$abbrevB."/gameinfo.html#nbaGIboxscore)**|
|&nbsp;|
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
		<table style='text-align: center'>
			<tr style='font-weight: bold'>
				<td style='text-align: left'><?php echo $teamA; ?></td>
				<td>MIN</td>
				<td>FGM-A</td>
				<td>3PM-A</td>
				<td>FTM-A</td>
				<td>OREB</td>
				<td>DREB</td>
				<td>REB</td>
				<td>AST</td>
				<td>STL</td>
				<td>BLK</td>
				<td>TO</td>
				<td>PF</td>
				<td>+/-</td>
				<td>PTS</td>
			</tr>
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
			$TOV = $playerData[22];
			$STL = $playerData[23];
			$BLK = $playerData[24];
			$PF = $playerData[25];
			$PTS = $playerData[26];
			$PM = $playerData[27];

			// SHOW ROW WITH NEW TEAM ONLY ONCE
			if ($city == $teamB && !$done) {
				?>
				<tr style='font-weight: bold'>
					<td style='text-align: left'><?php echo $teamB; ?></td>
					<td>MIN</td>
					<td>FGM-A</td>
					<td>3PM-A</td>
					<td>FTM-A</td>
					<td>OREB</td>
					<td>DREB</td>
					<td>REB</td>
					<td>AST</td>
					<td>STL</td>
					<td>BLK</td>
					<td>TO</td>
					<td>PF</td>
					<td>+/-</td>
					<td>PTS</td>
				</tr>
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
|^Generator: [^Excel](https://drive.google.com/file/d/0B81kEjcFfuavUmUyUk5OLVAtYzg/view?usp=sharing) ^by ^/u/imeanYOLOright  ^&  [^Web](http://nbaboxscoregenerator) ^by ^/u/jorgegil96|";
		?>
		</table>
		<hr>
		<h2>Copy text below to Reddit</h2>
		<p>Use Ctrl-A</p>
		<textarea cols="150" rows="50"><?php echo $textToReddit; ?></textarea>
		<?
	} else {
		echo "<h3> Boxscore not ready, wait for the game to end.</h3>";
	}
}


function short($player) {
	return substr($player, 0, 1).". ".strstr($player, " ");
}

?>







































