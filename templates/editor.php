<div id="st_editor">

<?php  
require 'editor_util.php'; 
$b = new Bestand($_);
?>
<p><?php $b->echoMessage(); ?></p>

<H2>Bestand</H2>
<br>

<?php if ($b->isEditable()) { ?>
<form action="update_bestand" name="update_bestand" method="post" accept-charset="UTF-8" >
<input type="hidden" id="id" name="id" value="<?php $b->echoBestandId(); ?>">
<?php } ?>

<table>
<tr><th>Nr:</th><td><input type="text" name="nr" id="nr" maxlength="20" value="<?php $b->echoNr(); ?>" /></td><td></td></tr>
<tr><th>Kategorie:</th><?php $b->echoKategorie(); ?></tr>
<tr><th>inventar_nr:</th><?php $b->echoInventar_nr(); ?></tr>
<tr><th>serien_nr:</th><?php $b->echoSerien_nr(); ?></tr>
<tr><th>weitere_nr:</th><?php $b->echoWeitere_nr(); ?></tr>
<tr><th>geheim_nr:</th><?php $b->echoGeheim_nr(); ?></tr>
<tr><th>bezeichnung:</th><?php $b->echoBezeichnung(); ?></tr>
<tr><th>typenbezeichnung:</th><?php $b->echoTypenbezeichnung(); ?></tr>
<tr><th>lieferant:</th><?php $b->echolieferant(); ?></tr>
<tr><th>standort:</th><?php $b->echoStandort(); ?></tr>
<tr><th>nutzer:</th><?php $b->echoNutzer(); ?></tr>
<tr><th>anschaffungswert:</th><?php $b->echoAnschaffungswert(); ?></tr>
<tr><th>st_beleg_nr:</th><?php $b->echoSt_beleg_nr(); ?></tr>
<tr><th>anschaffungsdatum:</th><?php $b->echoAnschaffungsdatum(); ?></tr>
<tr><th>zubehoer:</th><?php $b->echoZubehoer(); ?></tr>
<tr><th>st_inventar_nr:</th><?php $b->echoSt_inventar_nr(); ?></tr>
<tr><th>stb_inventar_nr:</th><?php $b->echoStb_inventar_nr(); ?></tr>
<tr><th>konto:</th><?php $b->echoKonto(); ?></tr>
<tr><th>ausgabedatum:</th><?php $b->echoAusgabedatum(); ?></tr>
<tr><th>ruecknahmedatum:</th><?php $b->echoRuecknahmedatum(); ?></tr>
<tr><th>prueftermin1:</th><?php $b->echoPrueftermin1(); ?></tr>
<tr><th>prueftermin2:</th><?php $b->echoPrueftermin2(); ?></tr>
<tr><th>bemerkung:</th><?php $b->echoBemerkung(); ?></tr>
<tr><th>fluke_nr:</th><?php $b->echoFluke_nr(); ?></tr>
</table>

<?php if ($b->isEditable()) { ?>
<input type="submit" name="submit" id="submit" value="speichern" />
</form>
<?php } ?>

<?php if (0 < $b->getBestandId()) { ?>
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
<form action="add_doc_bestand" name="add_doc_bestand" method="post" enctype="multipart/form-data">
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
