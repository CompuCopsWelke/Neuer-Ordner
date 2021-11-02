<div id="stundenzettel">
<?php  
require 'main_util.php'; 
$w = new Wochenblatt($_);
?>
<H2>
KW <?php  $w->echoCurrentWeek(); ?> von <?php $w->echoCurrentUser(); ?>
</H2>

<?php  $w->getLinksToPrevNextWeek(); ?>

<form action="<?php  $w->echoGotoWeek(); ?>" name="goto" method="post">
    <div id="goto" style="float: right;">
    <input type="submit" value="Gehe zu:">
    <input type="date" name="week" id="week" />
    <?php $w->selectMitarbeiter(); ?>
    </div>
</form>

<?php  $w->echoMessage(); ?>

<table border="1">
    <tr><th rowspan="2">F</th><th rowspan="2">A</th><th rowspan="2">Datum</th><th rowspan="2">von</th><th rowspan="2">bis</th><th rowspan="2">Auftrags-Nr.</th><th rowspan="2">Bauvorhaben</th><th rowspan="2">Stunden</th><th rowspan="2">Über-<br>stunden</th><th rowspan="2">Lohnart</th><th colspan="3">Zulage für Erschwernisse</th><th rowspan="2">RB</th><th rowspan="2">VMA</th><th rowspan="2">L</th</tr>
    <tr><th>Stunden</th><th>Nr</th><th>Tätigkeit</th></tr>
<?php  $w->showWochenblatt(); ?>
</table>

<?php  $w->echoCreateEintrag(); ?>

<form action="<?php  $w->echoPruefung(); ?>" name="hzettel_back" method="post" accept-charset="UTF-8">
<input type="hidden" id="wochenblatt_id" name="wochenblatt_id" value="<?php $w->echoWochenblatt_Id(); ?>">
    <p>
    <label>Bearbeitungskommentar:
        <input type="text" id="edit_comment" name="edit_comment">
    </label>
    </p>

    <?php  $w->echoStimmtSoButton(); ?>
    <button type="submit" name="not_ok" id="not_ok" value="not_ok">zurück</button> 

    <?php  $w->echoWeitergabeAn(); ?>
</form>

<p><H2>Ausfüllhinweise</H2>
Spaltenbezeichnung: F - Feiertag,
A - Arbeitszeitverlagerung,
RB - Rufbereitschaft,
VMA - Verpflegungsmehraufwand
L - Löschen
</p>

<p> <?php  $w->echoWochenExportLink(); ?> </p>

<p> <H2>Prüfungsübersicht</H2>
<?php  $w->echoLastEdit(); ?>
<table>
<tr><th>eingereicht am</th><th>Zuständiger</th><th>Kommentar</th><th>bestanden</th><th>geprüft am</th><th>geprüft von</th></tr>
<?php  $w->echoPruefungen();  ?>
</table></p>

<?php if ($w->isPruefungsBerechtigt()) {  ?>
<p><H2>offene Prüfungen</H2>

<?php  $w->echoPruefungFilter();  ?>

<table>
<tr><th>eingereicht am</th><th>KW</th><th>Mitarbeiter</th><th>Zuständiger</th><th>Kommentar</th></tr>
<?php  $w->echoOffenenPruefungen();  ?>
</table></p>
<?php }  # isPruefungsBerechtigt  ?> 
</div>
