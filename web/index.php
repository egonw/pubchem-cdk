<html>
  <head>
    <title>PubChem-CDK</title>
    <style type="text/css">
      .floatright { float: right; }
    </style>
  </head>

<body>

<?php 

include 'atomtyping/vars.php';

mysql_connect("localhost", $user, $pwd) or die(mysql_error());
# echo "<!-- Connection to the server was successful! -->\n";

mysql_select_db("pubchem") or die(mysql_error());
# echo "<!-- Database was selected! -->\n";

$molecule = $_GET["cid"];

if (!($molecule)) {

  echo "<h1>Statistics</h1>\n"; 
  echo "<ul>\n";

  $result = mysql_query("SELECT count(*) FROM compounds");
  $row = mysql_fetch_assoc($result);
  if ($row) {
    echo "<li>" . $row['count(*)'] . " compounds</li>";
  }

  $result = mysql_query("SELECT count(*) FROM atomtypeproblem");
  $row = mysql_fetch_assoc($result);
  if ($row) {
    echo "<li>" . $row['count(*)'] . " <a href=\"/pubchem/atomtyping/\">unrecognized atom types</a></li>";
  }

  echo "</ul>\n";

  echo "<p>Try CID <a href=\"/pubchem/?cid=139735\">139735</a> or element <a href=\"/pubchem/atomtyping/?element=C\">C</a>.\n";

} else {

$result = mysql_query("SELECT * FROM compounds WHERE CID = '$molecule'");

$row = mysql_fetch_assoc($result);

if ($row) {
  $molecule = $row['CID'];
  $inchi = $row['InChI'];
  $inchikey = $row['InChIKey']; 
  echo "<h1>CID: $molecule</h1>\n";

  echo "$inchi <br />\n";
  echo "$inchikey\n";

  echo "<p><a href=\"http://rdf.openmolecules.net/?$inchi\">rdf.openmolecules.net</a>\n";

  echo "<p><a href=\"http://pubchem.ncbi.nlm.nih.gov/summary/summary.cgi?cid=$molecule\"><img width=\"200\" src=\"http://pubchem.ncbi.nlm.nih.gov/image/imgsrv.fcgi?t=l&cid=$molecule\" class=\"floatright\"/></a>\n";

  $res2 = mysql_query("SELECT * FROM atomtypeproblem WHERE CID = '$molecule'");
  echo "<h2>Atom Type Perception Problems</h2>\n";
  echo "<table>\n";
  $problems = 0;
  while ($row2 = mysql_fetch_assoc($res2)) {
    $atom = $row2['atom'];
    $element = $row2['element'];
    echo "<tr><td>$atom</td><td><a href=\"/pubchem/atomtyping/?element=$element\">$element</a></td></tr>\n";
    $problems = $problems + 1;
  }
  echo "</table>";
  if ($problems == 0) {
    echo "<p>Atom types are recognized for all atoms.</p>\n";
  }

  echo "<h2>Bioclipse Script</h2>\n";
  echo "<pre>\n";
  echo "filename = \"/Virtual/".$molecule.".2d.xml\";\n";
  echo "pubchem.loadCompound($molecule, filename);\n";
  echo "ui.open(filename);\n";
  echo "mol = cdk.loadMolecule(filename)\n";
  echo "cdx.perceiveCDKAtomTypes(mol)\n";
  echo "</pre>\n";

} else {

}

}

?>

</html>
