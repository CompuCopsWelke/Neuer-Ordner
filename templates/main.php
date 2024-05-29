<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
script('bestand', 'main');
?>

<div id="bestand">
    <?php
    require 'main_util.php';
$b = new Bestandliste($_);
?>
    <form action="<?php $b->echoGotoIndex(); ?>" name="suche" id="suche" method="post">
        <label for="kategorie">Kategorie: </label><?php $b->selectKategorie(); ?>

        <?php $b->selectSuchfeld(); ?>
        <input type="text" maxlength="100" name="suchtext" id="suchtext" value="<?php $b->echoSuchtext(); ?>">

        <label for="datumfeld">Datum: </label><?php $b->selectDatumfeld(); ?>
        <input type="date" name="von" id="von" value="<?php $b->echoVon() ?>">
        <input type="date" name="bis" id="bis" value="<?php $b->echoBis() ?>">

        <label for="sort">Sortierung: </label><?php $b->selectSortierung(); ?>

        <input type="submit" value="Suche">
    </form>

    <?php $b->echoMessage(); ?>
    <?php $b->echoCreateBestand(); ?>

    <div id="bestand_table">
        <table>
            <thead>
            <tr>
                <?php $b->showTableHeader(); ?>
            </tr>
            </thead>
            <?php $b->showBestand(); ?>
        </table>
    </div>
</div>
