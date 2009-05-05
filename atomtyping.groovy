import org.openscience.cdk.interfaces.*;
import org.openscience.cdk.io.*;
import org.openscience.cdk.io.iterator.*;
import org.openscience.cdk.*;
import org.openscience.cdk.tools.manipulator.*;
import org.openscience.cdk.atomtype.*;
import org.openscience.cdk.nonotify.*;

import groovy.sql.Sql
import java.util.zip.GZIPInputStream

dbcfg = new Properties();
dbcfg.load(new File("db.cfg").newInputStream())
user = dbcfg.getProperty("user");
pwd = dbcfg.getProperty("pwd");

def sql = Sql.newInstance(
  "jdbc:mysql://localhost:3306/pubchem",
  "$user", "$pwd",
  "com.mysql.jdbc.Driver"
)

matcher = CDKAtomTypeMatcher.getInstance(
  NoNotificationChemObjectBuilder.getInstance()
);

dir = new File("XML")
def p = ~/Compound.*gz/
dir.eachFileMatch(p) {
  println it.name
  iterator = new IteratingPCCompoundXMLReader(
    new GZIPInputStream(it.newInputStream()),
    NoNotificationChemObjectBuilder.getInstance()
  )
  counter = 0
  while (iterator.hasNext()) {
    IMolecule mol = iterator.next()
    cid = mol.getProperty("PubChem CID")
    types = matcher.findMatchingAtomType(mol)
    for (int i=1; i<=types.length; i++) {
      if (types[i-1] == null) {
        element = mol.getAtom(i-1).symbol
        // println "insert into atomtypeproblem (cid, atom, element) values (${cid}, ${i}, ${element})"
        sql.execute("insert into atomtypeproblem (cid, atom, element) values (${cid}, ${i}, ${element})")
      }
    }
    if (counter++ == 100) { counter = 0; print "."; }
  }
  println ""
}


