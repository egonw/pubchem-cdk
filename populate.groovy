import org.openscience.cdk.interfaces.*;
import org.openscience.cdk.io.*;
import org.openscience.cdk.io.iterator.*;
import org.openscience.cdk.*;
import org.openscience.cdk.tools.manipulator.*;

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

dir = new File("XML")
def p = ~/Compound_.*gz/
dir.eachFileMatch(p) {
  println it.name
  iterator = new IteratingPCCompoundXMLReader(
    new GZIPInputStream(it.newInputStream()),
    DefaultChemObjectBuilder.getInstance()
  )
  counter = 0
  while (iterator.hasNext()) {
    IMolecule mol = iterator.next()
    cid = mol.getProperty("PubChem CID")
    hasEntry = false
    sql.eachRow("select * from compounds where CID = $cid") {
      hasEntry = true
    }
    if (!hasEntry) {
      inchi = mol.getProperty("InChI (Standard)")
      key = mol.getProperty("InChIKey (Standard)")
      sql.execute("insert into compounds (InChI, InChIKey, CID) values (${inchi}, ${key}, ${cid})")
    }
    if (counter++ == 100) { counter = 0; print "."; }
  }
  println ""
}


