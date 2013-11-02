<?php

//defining the global variables
define(RATING_MIN, 2.5);

$critics = array( 'Lisa Rose' => 
					array('Lady in the Water' => 2.5,
			        'Snakes on a Plane' => 3.5,
			        'Just My Luck' => 3.0,
			        'Superman Returns' => 3.5,
			        'You, Me and Dupree' => 2.5,
			        'The Night Listener' => 3.0),

			     'Lisa Rose2' => 
					array('Lady in the Water' => 2.5,
			        'Snakes on a Plane' => 3.5,
			        'Just My Luck' => 3.0,
			        'Superman Returns' => 3.5,
			        'You, Me and Dupree' => 2.5,
			        'The Night Listener' => 3.0),

				'Gene Seymour' => 
			        array('Lady in the Water' => 3.0,
			        'Snakes on a Plane' => 3.5,
			        'Just My Luck' => 1.5,
			        'Superman Returns' => 5.0,
			        'The Night Listener' => 3.0,
			        'You, Me and Dupree' => 3.5),
			    
			    'Michael Phillips' =>
			        array('Lady in the Water' => 2.5,
			        'Snakes on a Plane' => 3.0,
			        'Superman Returns' => 3.5,
			        'The Night Listener' => 4.0),
			    
			    'Claudia Puig' =>
			        array('Snakes on a Plane' => 3.5,
			        'Just My Luck' => 3.0,
			        'The Night Listener' => 4.5,
			        'Superman Returns' => 4.0,
			        'You, Me and Dupree' => 2.5),
			    
			    'Mick LaSalle' =>
			        array('Lady in the Water' => 3.0,
			        'Snakes on a Plane' => 4.0,
			        'Just My Luck' => 2.0,
			        'Superman Returns' => 3.0,
			        'The Night Listener' => 3.0,
			        'You, Me and Dupree' => 2.0),
			    
			    'Jack Matthews' =>
			        array('Lady in the Water' => 3.0,
			        'Snakes on a Plane' => 4.0,
			        'The Night Listener' => 3.0,
			        'Superman Returns' => 5.0,
			        'You, Me and Dupree' => 3.5),
			    
			    'Toby' =>
			    	array('Snakes on a Plane' => 4.5, 
			    		  'You, Me and Dupree' => 1.0,
			              'Superman Returns' => 4.0)

				);


$recommendedBooks = get_recommendations($critics, 'Toby');

if(sizeof($recommendedBooks) < 1){
	print "Sorry no recommendations found";
} else {
	foreach ($recommendedBooks as $key => $value) {
		print $value;
	}
}


//hack this to add the similarity factor of - based in same country, in same profession
function sim_distance($prefs, $p1, $p2){
	//returns the distance based on the similarity between the persons
	$sharedItems = array();
	$sum_sq = 0;
	//get the movie list for person 2
	$keys = array_keys($prefs[$p2]);
	//print_r($keys);

	//get the list of common things between the two
	foreach($prefs[$p1] as $k => $v){
		if(in_array($k, $keys)){
			$sharedItems[] = $k;
			//calculating the diiference in rating
			$diff = $prefs[$p2][$k] - $prefs[$p1][$k];
			$sum_sq += pow($diff, 2); 
		}
	}
	//echoing everything
	//print_r($sharedItems);

	//calculating the distance
	$dist = pow($sum_sq, 1/2);
	return 1 / (1 + $dist);
}

//this function will determine the order in which we should present the books
function top_matches($prefs, $p, $dist){
	//get the top rated books only
	$topBooks = array();
	foreach($prefs[$p] as $k => $v){
		if ($v > RATING_MIN){
			$newRating = $v*$dist;
			$topBooks[$k] = $newRating;
		}
	}
	arsort($topBooks);
	//print_r($topBooks);
	return $topBooks;
}

function get_recommendations($prefs, $p1){
	//get the similarity of one person with respect to other users // taking the top 3 distances
	//if he is very similar, then get the books from those that he has not read

	//saving the array in a different array
	$prefsOriginal = $prefs;

	//unsetting the person from the array - for whom we are getting the recommendation
	unset($prefs[$p1]);

	//array to save the person and the distance
	$personDistance = array();

	//calculating the similarity of this person with other users
	foreach($prefs as $k => $v){
		$dist = sim_distance($prefsOriginal, $p1, $k);
		$personDistance[$k] = $dist;
	}

	//sorting the array based on the distance - nearest first
	arsort($personDistance);

	//printing the top recommenders
	//print_r($personDistance);

	//getting the books from the distance people - taking top 3
	$i = 0;
	$recommendedBooks = array();
	$topBooks = array();
	foreach($personDistance as $k => $v){
		//get the top books for this person
		$topBooks = top_matches($prefs, $k, $v);
		$recommendedBooks[] = $topBooks;
		$i += 1;
		if($i > 2) break; //condition to take the recommendations only from the top matches
	}
	//printing the combine array
	//print_r($recommendedBooks);
	
	//merging the arrays
	$recBooks = array_merge($recommendedBooks[0], $recommendedBooks[1], $recommendedBooks[2]);

	//getting the name of the books
	$recBooks = array_keys($recBooks);
	//print_r($recBooks);

	//subtracting the items that the user has already seen
	foreach($prefsOriginal[$p1] as $k => $v){
		if (in_array($k, $recBooks)){
			$key = array_search($k, $recBooks);
			//print $key;
			unset($recBooks[$key]);
		}
	}

	//printing after removing the read books
	//print_r($recBooks);

	//getting the order of the books in which to display based on the rating match
	//Might do this in future - My present algo takes a lot of time - order n^3

	return $recBooks;

}

?>