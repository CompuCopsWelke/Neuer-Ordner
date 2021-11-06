<div id="bestand">
<?php  
require 'main_util.php'; 
$b = new Bestandliste($_);
?>

<form action="<?php  $b->echoGotoIndex(); ?>" name="suche" method="post">
    Kategorie: <?php  $b->selectKategorie(); ?>

    <?php  $b->selectSuchfeld(); ?>
    <input type="text" maxlenght="100" name="suchtext" id="suchtext" value="<?php $b->echoSuchtext(); ?>" />

    Datum: <?php $b->selectDatumfeld(); ?>
    <input type="date" name="von" id="von" value="<?php $b->echoVon() ?>" />
    <input type="date" name="bis" id="bis" value="<?php $b->echoBis() ?>" />
 
     Sortierung:  <?php  $b->selectSortierung(); ?>

    <input type="submit" value="Suche">
</form>

<?php  $b->echoMessage(); ?>
<?php  $b->echoCreateBestand(); ?>

<table border="1">
    <tr>
    <th>Kategorie</th>
    <th>Inventar-Nr</th>
    <th>Serien-Nr</th>
    <th>Weitere Nr</th>
    <th>Bezeichnung</th>
    <th>Typenbezeichnung</th>
    <th>Lieferant</th>
    <th>Standort</th>
    <th>Nutzer</th>
    <th>Anschaffungswert</th>
    <th>SB-Beleg-nr</th>
    <th>Anschaffungsdatum</th>
    <th>Zubehör</th>
    <th>St-inventar-nr</th>
    <th>StB-Inventar-Nr</th>
    <th>Konto</th>
    <th>Ausgabedatum</th>
    <th>Rücknahmedatum</th>
    <th>Prüftermin1</th>
    <th>Prüftermin2</th>
    <th>Bemerkung</th>
    <th>Fluke-Nr</th>
    </tr>
<?php  $b->showBestand(); ?>
</table>
</div>
