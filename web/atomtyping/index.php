<html>
  <head>
    <title>PubChem-CDK</title>
  </head>

<body>

<?php 

include 'vars.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db("pubchem") or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$element = $_GET["element"];

if (!($element)) {

  echo "<h1>Statistics</h1>\n";

  $result = mysql_query("SELECT element, count(element) FROM atomtypeproblem GROUP BY element");

  echo "<table>\n";
  while ($row = mysql_fetch_assoc($result)) {
    $element = $row['element'];
    $amount = $row['count(element)'];
    echo "<tr><td><a href=\"/pubchem/atomtyping/?element=$element\">$element</a></td><td>$amount</td></tr>\n";
  }
  echo "</table>\n";

} else {

echo "<h1>Element: $element</h1>\n";

$result = mysql_query("SELECT *, count(cid) FROM atomtypeproblem WHERE element = '$element' GROUP BY cid");

while ($row = mysql_fetch_assoc($result)) {
  $cid = $row['cid'];
  $amount = $row['count(cid)'];
  echo "<a href=\"http://pele.farmbio.uu.se/pubchem/?cid=$cid\">$cid</a>";
  if ($amount > 1) echo "*" . $amount;
  echo " \n";
}

}

?>

</html>
