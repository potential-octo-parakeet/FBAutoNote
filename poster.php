<?php
	require 'appConfig.php';

	// assume total pageids and notes 
	// rows are already set to cookie
	$TotalPagesRow = isset($_COOKIE['TotalPagesRow']) ? $_COOKIE['TotalPagesRow'] : 0;
	$TotalNotesRow = isset($_COOKIE['TotalNotesRow']) ? $_COOKIE['TotalNotesRow'] : 0;

	// read both files notes and page ids
	$Notes = file('notes.csv');
	$Pages = file('pageids');

	$PageRow = isset($_COOKIE['PageRow']) ? $_COOKIE['PageRow'] : 0;
	$NoteRow = isset($_COOKIE['NoteRow']) ? $_COOKIE['NoteRow'] : 0;

  list($PageID,$AccessToken) = explode(":",$Pages[$PageRow]);
  list($Title,$Content) = explode("^",$Notes[$NoteRow]);

  $note = array('access_token' => $AccessToken, 'subject' => $Title, 'message' => $Content);

  try{

		$result = $fb->api('/'.$PageID.'/notes','post',$note);

		if($PageRow<$TotalPagesRow){
			$PageRow++;
		}

		if($NoteRow<$TotalNotesRow){
			$NoteRow++;
		}

		if($PageRow>=$TotalPagesRow){
			$PageRow = 0;
		}

		setcookie('NoteRow',$NoteRow);
		setcookie('PageRow',$PageRow);

		// result
		echo "http://fb.com/".$result['id'];

	} catch(FacebookApiException $e){
		//echo $e->message;
		//print_r($note);
		//echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
		echo "An error has occured. Possible account restriction to Facebook API /notes.";
	}