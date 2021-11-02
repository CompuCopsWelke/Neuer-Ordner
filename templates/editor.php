<div id="st_editor">

<?php  
require 'editor_util.php'; 

$e = new Eintrag($_);
?>
<p><?php $e->echoMessage(); ?></p>

<form action="update_eintrag" name="update_eintrag" method="post" accept-charset="UTF-8" >

<input type="hidden" id="id" name="id" value="<?php $e->echoEntryId(); ?>">
<input type="hidden" id="uid" name="uid" value="<?php $e->echoUid(); ?>">

<table>
<tr><th>Mitarbeiter:</th><td><?php $e->echoUid(); ?></td></tr>

<tr><th>Feiertag:</th><td><input type="checkbox" name="feiertag" id="feiertag" value="feiertag" <?php $e->echoFeiertagChecked(); ?>/></td></tr>
<tr><th>Arbeitszeitverlagerung:</th><td><input type="checkbox" name="arbeitszeitverlagerung" id="arbeitszeitverlagerung" value="arbeitszeitverlagerung" <?php $e->echoArbeitszeitverlagerungChecked(); ?> /></td></tr>

<tr><th>Datum:</th><td><input type="date" name="datum" id="datum" value="<?php $e->echoDatum(); ?>" /></td></tr>

<tr><th>Uhrzeit von:</th><td><input type="time" name="von" id="von" value="<?php $e->echoVon(); ?>" /> </td></tr>
<tr><th>Uhrzeit bis:</th><td><input type="time" name="bis" id="bis" value="<?php $e->echoBis(); ?>" /> </td></tr>

<tr><th>Auftragsnr:</th><td><input type="text" maxlenght="12" name="auftragsnr" id="auftragsnr" value="<?php $e->echoAuftragsnr(); ?>" /> </td></tr>
<tr><th>Bauvorhaben:</th><td><textarea rows="3" maxlength="1024" name="bauvorhaben" id="bauvorhaben"><?php $e->echoBauvorhaben(); ?></textarea></td></tr>

<tr><th>Stunden: </th><td><input type="number" step="any" lang="de" pattern="[0-9]+[,.]*[0-9]*" name="stunden" id="stunden" value="<?php $e->echoStunden(); ?>" /> </td></tr>
<tr><th>Überstunden: </th><td><input type="checkbox" name="ueberstunden" id="ueberstunden" value="ueberstunden" <?php $e->echoUeberstundenChecked(); ?> /> </td></tr>

<?php $e->echoLohnartEditor(); ?>

<tr><th>Erschwer.-Stunden:</th><td><input type="number" step="any" lang="de" pattern="[0-9]+[,.]*[0-9]*" name="erschwer_stunden" id="erschwer_stunden" value="<?php $e->echoErschwerStunden(); ?>" /> </td></tr>

<tr><th>Erschwer.-Nr.:</th><td><input type="text" maxlength="12" name="erschwer_nr" id="erschwer_nr" value="<?php $e->echoErschwerNr(); ?>" /> </td></tr>

<tr><th>Erschwer.-Tätigkeit:</th><td><textarea rows="3" maxlength="1024" name="erschwer_taetigkeit" id="erschwer_taetigkeit"><?php $e->echoErschwerTaetigkeit(); ?></textarea></td></tr>

<tr><th>Rufbereitschaft:
</th><td><input type="checkbox" name="rufbereitschaft" id="rufbereitschaft" value="rufbereitschaft" <?php $e->echoRufbereitschaftChecked(); ?> />
</td></tr>
<tr><th>Verpflegungsmehraufwand:
</th><td><input type="number" step="any" lang="de" pattern="[0-9]+[,.]*[0-9]*" name="verpflegungsmehraufwand" id="verpflegungsmehraufwand"  value="<?php $e->echoVerpflegungsmehraufwand(); ?>"/>
</td></tr>
</table>

<input type="submit" name="submit" id="submit" value="speichern" />
<?php $e->echoSubmitNext(); ?>
<?php $e->echoTableLink(); ?>

</form>
</div>
