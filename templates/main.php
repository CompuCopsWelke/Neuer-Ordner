<div id="bestand">
<?php  
require 'main_util.php'; 
$b = new Bestandliste($_);
?>

<form action="<?php  $b->echoGotoIndex(); ?>" name="suche" method="post">
    Kategorie:
    <?php  $b->selectKategorie(); ?>

    <?php  $b->selectSuchfeld(); ?>

    <input type="text" maxlenght="100" name="suchtext" id="suchtext" value="<?php $b->echoSuchtext(); ?>" />

    <?php $b->selectDatumfeld(); ?>
    <input type="date" name="von" id="von" value="<?php $b->echoVon() ?>" />
    <input type="date" name="bis" id="bis" value="<?php $b->echoBis() ?>" />

    <input type="submit" value="Suche">
</form>

<?php  $b->echoMessage(); ?>
<?php  $b->echoCreateBestand(); ?>

<table border="1">
    <tr>
    <th>Kategorie</th>
    <th>inventar_nr</th>
    <th>serien_nr</th>
    <th>weitere_nr</th>
    <th>geheim_nr</th>
    <th>bezeichnung</th>
    <th>typenbezeichnung</th>
    <th>lieferant</th>
    <th>standort</th>
    <th>nutzer</th>
    <th>anschaffungswert</th>
    <th>st_beleg_nr</th>
    <th>anschaffungsdatum</th>
    <th>zubehoer</th>
    <th>st_inventar_nr</th>
    <th>stb_inventar_nr</th>
    <th>konto</th>
    <th>ausgabedatum</th>
    <th>ruecknahmedatum</th>
    <th>prueftermin1</th>
    <th>prueftermin2</th>
    <th>bemerkung</th>
    <th>fluke_nr</th>
    </tr>
<?php  $b->showBestand(); ?>
</table>
</div>
