<div id="st_editor">
    <?php
    require 'editor_util.php';
    $b = new Bestand($_);
    ?>
    <p><?php $b->echoMessage(); ?></p>

    <H2>Bestand</H2>

    <?php if ($b->isEditable()) { ?>
    <form action="<?php $b->echoUpdateTeil(); ?>" name="update_bestand" method="post" accept-charset="UTF-8">
        <input type="hidden" id="id" name="id" value="<?php $b->echoBestandId(); ?>">
        <input type="hidden" id="letzte_kategorie" name="letzte_kategorie" value="<?php $b->echoLetzteKategorie(); ?>">
        <?php } ?>

        <table>
            <tr>
                <th><label for="kategorie">Kategorie:</label></th><?php $b->echoKategorie(); ?></tr>
            <tr>
                <th><label for="inventar_nr">Inventar-Nr:</label></th><?php $b->echoInventar_nr(); ?></tr>
            <tr>
                <th><label for="serien_nr">Serien-Nr:</label></th><?php $b->echoSerien_nr(); ?></tr>
            <tr>
                <th><label for="weitere_nr">weitere Nr:</label></th><?php $b->echoWeitere_nr(); ?></tr>
            <tr>
                <th><label for="geheim_nr">Geheim-Nr:</label></th><?php $b->echoGeheim_nr(); ?></tr>
            <tr>
                <th><label for="bezeichnung">Bezeichnung:</label></th><?php $b->echoBezeichnung(); ?></tr>
            <tr>
                <th><label for="typenbezeichnung">Typenbezeichnung:</label></th><?php $b->echoTypenbezeichnung(); ?>
            </tr>
            <tr>
                <th><label for="lieferant">Lieferant:</label></th><?php $b->echolieferant(); ?></tr>
            <tr>
                <th><label for="standort">Standort:</label></th><?php $b->echoStandort(); ?></tr>
            <tr>
                <th><label for="nutzer">Nutzer:</label></th><?php $b->echoNutzer(); ?></tr>
            <tr>
                <th><label for="einsatzort">Einsatzort:</label></th><?php $b->echoEinsatzort(); ?></tr>
            <tr>
                <th><label for="anschaffungswert">Anschaffungswert:</label></th><?php $b->echoAnschaffungswert(); ?>
            </tr>
            <tr>
                <th><label for="st_beleg_nr">ST-Beleg-Nr:</label></th><?php $b->echoSt_beleg_nr(); ?></tr>
            <tr>
                <th><label for="anschaffungsdatum">Anschaffungsdatum:</label></th><?php $b->echoAnschaffungsdatum(); ?>
            </tr>
            <tr>
                <th><label for="zubehoer">Zubehör:</label></th><?php $b->echoZubehoer(); ?></tr>
            <tr>
                <th><label for="st_inventar_nr">ST-Inventar-Nr:</label></th><?php $b->echoSt_inventar_nr(); ?></tr>
            <tr>
                <th><label for="stb_inventar_nr">STB-Inventar-Nr:</label></th><?php $b->echoStb_inventar_nr(); ?></tr>
            <tr>
                <th><label for="konto">Konto:</label></th><?php $b->echoKonto(); ?></tr>
            <tr>
                <th><label for="ausgabedatum">Ausgabedatum:</label></th><?php $b->echoAusgabedatum(); ?></tr>
            <tr>
                <th><label for="ruecknahmedatum">Rücknahmedatum:</label></th><?php $b->echoRuecknahmedatum(); ?></tr>
            <tr>
                <th><label for="prueftermin1">Prüftermin 1:</label></th><?php $b->echoPrueftermin1(); ?></tr>
            <tr>
                <th><label for="prueftermin2">Prüftermin 2:</label></th><?php $b->echoPrueftermin2(); ?></tr>
            <tr>
                <th><label for="bemerkung">Bemerkung:</label></th><?php $b->echoBemerkung(); ?></tr>
            <tr>
                <th><label for="fluke_nr">Fluke-Nr:</label></th><?php $b->echoFluke_nr(); ?></tr>
        </table>

        <?php if ($b->isEditable()) { ?>
        <input type="submit" name="submit" id="submit" value="Speichern">
    </form>
<?php $b->echoDeleteTeil();
        }
    $b->echoCreateTeil();
    if (0 < $b->getBestandId()) { ?>
    <br>
    <div id="bestand">
        <hr>
        <H3>Dokumente</H3>
        <table class="editor_table">
            <tr>
                <th>Titel</th>
                <th>Dateiname</th>
                <th></th>
            </tr>
            <?php $b->echoDocTable(); ?>
        </table>
        <br>

        <?php if ($b->isEditable()) { ?>
            <form action="<?php $b->echoAddDocLink(); ?>" name="add_doc_bestand" method="post"
                  enctype="multipart/form-data">
                <input type="hidden" id="bestand_id" name="bestand_id" value="<?php $b->echoBestandId(); ?>">
                <label for="titel">Dokumenttitel: </label><input type="text" name="titel" id="titel" maxlength="255"
                                                                 style="width:400px"><br>
                <input type="file" id="datei_document" name="datei_document"><br>
                <input type="submit" name="submit_doc" id="submit_doc" value="hinzufügen">
            </form>
        <?php } // if ($b->isEditable())?>

        <hr>
        <H3>Ausgabe-Historie</H3>
        <table class="editor_table">
            <tr>
                <th>Ausgabe</th>
                <th>Rückname</th>
                <th>Nutzer</th>
                <th>Einsatzort</th>
            </tr>
            <?php $b->echoLeihHistorie(); ?>
        </table>

    </div>
<?php } // if (0 < $b->getBestandId())?>
</div>
