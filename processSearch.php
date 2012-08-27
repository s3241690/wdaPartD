<html>
	<head>
		<Title>WDA Assignment 1: Wine Database Results Page</Title>
		<link rel="stylesheet" href="style.css" type="text/css" />
	</head>
	
	<body>

<?php

require_once('connect.inc');




/* Collect GET Search Criteria */
if($_GET['wine'] != null)
	$wine = $_GET['wine'];

if($_GET['winery'] != null)
	$winery = $_GET['winery'];

if($_GET['region'] != null)
	$region = $_GET['region'];

if($_GET['grapeVariety'] != null)
	$grapeVariety = $_GET['grapeVariety'];
	
if($_GET['yearLowerBound'] != null)
	$yearLowerBound = $_GET['yearLowerBound'];
	
if($_GET['yearUpperBound'] != null)
	$yearUpperBound = $_GET['yearUpperBound'];
	
if($_GET['minWinesInStock'] != null)
	$minWinesInStock = $_GET['minWinesInStock'];
	
if($_GET['minWinesOrdered'] != null)
	$minWinesOrdered = $_GET['minWinesOrdered'];
	
if($_GET['costLowerBound'] != null)
	$costLowerBound = $_GET['costLowerBound'];
	
if($_GET['costUpperBound'] != null)
	$costUpperBound = $_GET['costUpperBound'];
	




/*
 *	Error Checking
 *	Adds a binary bit value for each error to the $errors variable
 *	(e.g. error #1 is worth 1, error #2 2, both is worth 3)
 *	The Decimal variable is then returned and decoded to switch 
 *	on it's respective error messages on the search page. 
*/
$errors = null;


/* Years is Invalid */
if($yearLowerBound > $yearUpperBound)
	$errors += 1;
 
/* Cost is Invalid */
if($costLowerBound > $costUpperBound)
	$errors += 2;
	


/* Create a return string for back button and incase of errors */
$returnString ='search.php?wine='.$wine.'&winery='.$winery.'&region='
						.$region.'&grapeVariety='.$grapeVariety.'&yearLowerBound='
						.$yearLowerBound.'&yearUpperBound='.$yearUpperBound.'&minWinesInStock='
						.$minWinesInStock.'&minWinesOrdered='.$minWinesOrdered.'&costLowerBound='
						.$costLowerBound.'&costUpperBound='.$costUpperBound;

/* If there are errors add them to the return string and return to search page */
if ($errors != null)
{
	$returnString .= '&errors='.$errors;

	header('Location: '.$returnString);
}

/*  Create Base Wine SQL Query */
	$query = "SELECT wine.wine_id, wine_name, "
			."(SELECT GROUP_CONCAT( CAST( cost AS CHAR ) ) FROM inventory WHERE wine.wine_id = inventory.wine_id) as price, "
			."GROUP_CONCAT( variety ) as variety, year, winery_name, region_name, "
			."(SELECT SUM( on_hand ) FROM inventory WHERE wine.wine_id = inventory.wine_id) as available, "
			."(SELECT SUM(qty) FROM items WHERE wine.wine_id = items.wine_id) as total_sold, "
			."(SELECT SUM(price) FROM items WHERE wine.wine_id = items.wine_id) as total_revenue "
			."FROM winery, region, wine, grape_variety, wine_variety, inventory "
			."WHERE winery.region_id = region.region_id "
			."AND wine.winery_id = winery.winery_id "
			."AND wine_variety.variety_id = grape_variety.variety_id "
			."AND wine.wine_id = wine_variety.wine_id "
			."AND wine.wine_id = inventory.wine_id ";

/* If wine name selected add as criteria */
if(isset($wine)){
  $query .= " AND wine.wine_name LIKE '{$wine}'";
}

/* If winery name selected add as criteria */
if(isset($winery)){
  $query .= " AND winery.winery_name LIKE '{$winery}'";
}

/* If specific region selected add as criteria */
if(isset($region) && $region != "All") {
  $query .= " AND region.region_name = '{$region}'";
}


/* If yearLowerBound selected add as criteria */
if(isset($yearLowerBound)){
	$query .=" AND wine.year >= {$yearLowerBound} ";
}

/* If yearUpperBound selected add as criteria */
if(isset($yearUpperBound)){
	$query .=" AND wine.year <= {$yearUpperBound} ";
}

/* If either costLowerBound or costUpperBound are set add base IN subquery criteria */
/* then bolt on the conditions respectively and close the bracket*/

if(isset($costLowerBound)){

	$query .="AND wine.wine_id IN (SELECT wine.wine_id FROM wine, inventory WHERE wine.wine_id = inventory.wine_id ";

	if (isset($costLowerBound)){
		$query .="AND cost >= {$costLowerBound} ";
	}
	if (isset($costUpperBound)){
		$query .="AND cost <= {$costUpperBound} ";
	}	
	
	$query .=")";

}

/* If grape_variety selected create IN subquery so that all the varieties of the wine can still be Concatinated in the main select clause*/	
if(isset($grapeVariety)){
 $query .="AND wine.wine_id IN (SELECT wine.wine_id FROM wine, wine_variety, grape_variety WHERE wine_variety.variety_id "
		."= grape_variety.variety_id AND wine.wine_id = wine_variety.wine_id AND grape_variety.variety LIKE 'RED') ";
}
 
/* Group by Wine ID*/ 
	$query .="GROUP BY wine.wine_id ";


/* If Minimum wines in Stock selected add as criteria */
if (isset($minWinesInStock)){
	$query .=" HAVING total_on_hand >= {$minWinesInStock}";
}

/* If Minimum wines Ordered selected add as criteria */
if (isset($minWinesOrdered)){
	$query .=" AND total_ordered > {$minWinesOrdered}";
}





/* Run the query on the server */

$queryResult = mysql_query($query, $dbconn);


					 $index = 0;
					 while($row = mysql_fetch_assoc($queryResult)) {
						$wineDetails[$index] = $row;;
						$index++;
					}
					

echo '<h1>WineStore Database: Search Results</h1>';


/* Create Back Button*/
echo '<a href="'.$returnString.'"><--Back</a> <a href="search.php">New Search</a>';
echo '<br />';

/* Check if there are any Results and display*/
$tableSize = count($wineDetails);
if ($tableSize > 0)
{
	echo $num_of_rows;
	


	echo '<table>';
		
	echo '<th>Wine Name</th>';
	echo '<th>Winery Name</th>';
	echo '<th>Grape Variety</th>';
	echo '<th>Region</th>';
	echo '<th>Price</th>';
	echo '<th>Available</th>';
	echo '<th>Total Sold</th>';
	echo '<th>Total Revenue</th>';



		
			for($t=0;$t<$tableSize;$t++)
			{
				echo '<tr>';
					
					echo '<td>';
						echo $wineDetails[$t]['wine_name'];
					echo '</td>';
							
					echo '<td>';
						echo $wineDetails[$t]['winery_name'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['variety'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['region_name'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['price'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['available'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['total_sold'];
					echo '</td>';

					echo '<td>';
						echo $wineDetails[$t]['total_revenue'];
					echo '</td>';				
				
				echo '</tr>';
			}
		
	echo '</table>';
}
else
{
	echo "<p>Your Query returned no results.</p>";
}

?>


</body>
</html> 

