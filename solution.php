<?php
/**
Shawn Simpson
simpson.shawn.r@gmail.com
**/

function parse_request($request, $secret)
{
	 if (strpos($request, ".") !== 88) return false;
	 $encoded_all     = strtr($request, '-_', '+/');
	 $encoded_sig     = substr($encoded_all, 0, 88);
	 $encoded_request = substr($encoded_all, 89);

	 $request = base64_decode($encoded_request);
	 $payload = json_decode($request, true);
 
	 return $payload;
}

function dates_with_at_least_n_scores($pdo, $n)
{
	$sql = "SELECT `date` FROM scores
GROUP BY `date`
HAVING COUNT(*) >= $n
ORDER by `date` DESC";

	$rtn = array(); //hold the date values to be returned
	foreach($pdo->query($sql) as $row) {
		$rtn[] = $row['date'];
	}
	
	return $rtn;
}

function users_with_top_score_on_date($pdo, $date)
{
	$sql = <<<HERE
	
	SELECT user_id FROM scores
 WHERE score IN (SELECT MAX(score) FROM scores WHERE `date`="$date")
 AND `date`="$date"
 ORDER BY user_id ASC
 
HERE;

	$rtn = array(); //hold the user_ids values to be returned
	foreach($pdo->query($sql) as $row) {
		$rtn[] = $row['user_id'];
	}
	
	return $rtn;
}

function dates_when_user_was_in_top_n($pdo, $user_id, $n)
{
	$sql_scores = <<<HERE
	SELECT `date`, group_concat(`score` ORDER BY `score` DESC) AS scs FROM scores
           GROUP BY `date`
           ORDER BY `date` DESC
HERE;
    
	$ordered_grouped_scores = array();
	foreach($pdo->query($sql_scores) as $row) {
		$ordered_grouped_scores[$row['date']] = explode(',', $row['scs']);
	}
	
	$sql_users_scores = <<<SQL
	SELECT `date`, `score` FROM scores
            WHERE user_id=$user_id
            ORDER BY `date` DESC
SQL;

	$rtn_dates = array();
	foreach($pdo->query($sql_users_scores) as $row) {
		for($i=0; $i<$n; $i++) {
			if($ordered_grouped_scores[$row['date']][$i] <= $row['score']) {
				$rtn_dates[] = $row['date'];
				break;
			}
		}
	}
	
	return $rtn_dates;
}

