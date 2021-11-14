<div id="st_editor">

<?php  
require 'editor_util.php'; 
$b = new Bestand($_);
?>
<p><?php $b->echoMessage(); ?></p>

<H2>Bestand</H2>

<?php if ($b->isEditable()) { ?>
<form action="<?php  $b->echoUpdateTeil(); ?>" name="update_bestand" method="post" accept-charset="UTF-8" >
<input type="hidden" id="id" name="id" value="<?php $b->echoBestandId(); ?>">
<?php } ?>

<table>
<tr><th>Kategorie:</th><?php $b->echoKategorie(); ?></tr>
<tr><th>Inventar-Nr:</th><?php $b->echoInventar_nr(); ?></tr>
<tr><th>Serien-Nr:</th><?php $b->echoSerien_nr(); ?></tr>
<tr><th>weitere Nr:</th><?php $b->echoWeitere_nr(); ?></tr>
<tr><th>Geheim-Nr:</th><?php $b->echoGeheim_nr(); ?></tr>
<tr><th>Bezeichnung:</th><?php $b->echoBezeichnung(); ?></tr>
<tr><th>Typenbezeichnung:</th><?php $b->echoTypenbezeichnung(); ?></tr>
<tr><th>Lieferant:</th><?php $b->echolieferant(); ?></tr>
<tr><th>Standort:</th><?php $b->echoStandort(); ?></tr>
<tr><th>Nutzer:</th><?php $b->echoNutzer(); ?></tr>
<tr><th>Anschaffungswert:</th><?php $b->echoAnschaffungswert(); ?></tr>
<tr><th>ST-Beleg-Nr:</th><?php $b->echoSt_beleg_nr(); ?></tr>
<tr><th>Anschaffungsdatum:</th><?php $b->echoAnschaffungsdatum(); ?></tr>
<tr><th>Zubehör:</th><?php $b->echoZubehoer(); ?></tr>
<tr><th>ST-Inventar-Nr:</th><?php $b->echoSt_inventar_nr(); ?></tr>
<tr><th>STB-Inventar-Nr:</th><?php $b->echoStb_inventar_nr(); ?></tr>
<tr><th>Konto:</th><?php $b->echoKonto(); ?></tr>
<tr><th>Ausgabedatum:</th><?php $b->echoAusgabedatum(); ?></tr>
<tr><th>Rücknahmedatum:</th><?php $b->echoRuecknahmedatum(); ?></tr>
<tr><th>Prüftermin 1:</th><?php $b->echoPrueftermin1(); ?></tr>
<tr><th>Prüftermin 2:</th><?php $b->echoPrueftermin2(); ?></tr>
<tr><th>Bemerkung:</th><?php $b->echoBemerkung(); ?></tr>
<tr><th>Fluke-Nr:</th><?php $b->echoFluke_nr(); ?></tr>
</table>

<?php if ($b->isEditable()) { ?>
<input type="submit" name="submit" id="submit" value="Speichern" />
</form>
<?php  $b->echoDeleteTeil(); 
    }
$b->echoCreateTeil();
if (0 < $b->getBestandId()) { ?>
<br>
<div id="bestand">
<hr>
<H3>Dokumente</H3>
<table>
<tr> <th>Titel</th><th>Dateiname</th><th></th> </tr>
<?php $b->echoDocTable(); ?>
</table>
<br>

<?php if ($b->isEditable()) { ?>
<form action="<?php $b->echoAddDocLink(); ?>" name="add_doc_bestand" method="post" enctype="multipart/form-data">
<input type="hidden" id="bestand_id" name="bestand_id" value="<?php $b->echoBestandId(); ?>">
Dokumenttitel: <input type="text" name="titel" id="titel"  maxlength="255" style="width:200px"/><br>
<input type="file" id="datei_document" name="datei_document"><br>
<input type="submit" name="submit" id="submit" value="hinzufügen" />
</form>
<?php 
  } // if ($b->isEditable())
} // if (0 < $b->getBestandId())  
?>
</div>
</div>
