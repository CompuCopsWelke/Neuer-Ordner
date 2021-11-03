<?php


class Bestandliste 
{
    /** @var PDO $dbh */
    private $dbh;
    private $urlGenerator;

    private $kategorie;
    private $suchfeld;
    private $suchtext;
    private $datumfeld;
    private $von;
    private $bis;


    /**
     * Bestandliste constructor.
     * @throws Exception
     */
    public function __construct($post_arr)
    {
        $this->kategorie = array_key_exists('kategorie', $post_arr ) ? $post_arr['kategorie'] : '';
        $this->suchfeld = array_key_exists( 'suchfeld', $post_arr) ? $post_arr['suchfeld'] : '';
        $this->suchtext = array_key_exists( 'suchtext', $post_arr) ? $post_arr['suchtext'] : '';

        $this->datumfeld = array_key_exists( 'datumfeld', $post_arr) ? $post_arr['datumfeld'] : '';
        $this->von = array_key_exists( 'von', $post_arr) ? $post_arr['von'] : '';
        $this->bis = array_key_exists( 'bis', $post_arr) ? $post_arr['bis'] : '';

        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $this->message = 'no login ????';
            return;
        }

        $this->getDbh();

        $this->urlGenerator = \OC::$server->getURLGenerator();
        # setlocale(LC_TIME,'de_DE.utf8'); 
    }

    private function getDbh()
    {
        include('bestand/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $this->dbh = new PDO($conn, $db_config['user'], $db_config['password']);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function echoMessage()
    {
        echo('<b>'.$this->message.'</b>');
    }

    public function echoGotoIndex()
    {
        echo($this->urlGenerator->linkToRoute('bestand.page.index', []));
    }

    public function echoCreateBestand()
    {
        $params['kategorie'] = $this->kategorie;
        $absoluteUrl = $this->urlGenerator->linkToRoute('bestand.editor.create', $params);

        echo('<p><a href="' . $absoluteUrl . '">neu</a>');
    }


    public function selectKategorie()
    {
        echo('<select name="kategorie">');
        echo('<option value="">&lt;alle&gt;</option>');

        $sql = 'SELECT id, name FROM oc_bdb_kategorie ORDER BY 2;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();

        while ($content = $stmt->fetch()) {
            $s = htmlspecialchars($content['name']);
            echo('<option value="'.$content['id'].'">' . s . '</option>');
        }
        $stmt->closeCursor();
        echo('</select>');
    }

    public function selectSuchfeld()
    {
        echo('<select name="suchfeld">');
        $feldList = [
            ['inventar_nr', 'Inventar-Nr'], 
            ['serien_nr', 'serien_nr'],
            ['weitere_nr', 'weitere_nr'],
            ['geheim_nr', 'geheim_nr'],
            ['bezeichnung', 'bezeichnung'],
            ['typenbezeichnung', 'Typenbezeichnung'],
            ['lieferant', 'lieferant'],
            ['standort', 'standort'],
            ['nutzer', 'nutzer'],
            ['st_beleg_nr', 'st_beleg_nr'],
            ['zubehoer', 'zubehoer'],
            ['st_inventar_nr', 'st_inventar_nr'],
            ['stb_inventar_nr', 'STB-Inventar-Nr'],
            ['konto', 'Konto'],
            ['bemerkung', 'Bemerkung'],
            ['fluke_nr', 'Fluke-Nr']
        ];

        # TODO last selected anzeigen
        echo('<option value="">&lt;Standard&gt;</option>');
        foreach ($feldList as $f) {
            echo('<option value="' . $f[0] . '">' . $f[1] . '</option>');
        }
        echo('</select>');
    }


    public function selectDatumfeld()
    {
        echo('<select name="datum">');
        $feldList = [
            ['anschaffungsdatum', 'Anschaffungsdatum'],
            ['ausgabedatum', 'Ausgabedatum'],
            ['ruecknahmedatum', 'Rücknahmedatum'],
            ['prueftermin1', 'Prüftermin1'],
            ['prueftermin2', 'Prüftermin2']
        ];

        # TODO last selected anzeigen
        echo('<option value="">&lt;bitte auswählen&gt;</option>');
        foreach ($feldList as $f) {
            echo('<option value="' . $f[0] . '">' . $f[1] . '</option>');
        }
        echo('</select>');
    }
    
    public function showBestand()
    {
        $sql = "SELECT b.id, k.name,
            b.inventar_nr,
            b.serien_nr,
            b.weitere_nr,
            b.geheim_nr,
            b.bezeichnung,
            b.typenbezeichnung,
            b.lieferant,
            b.standort,
            b.nutzer,
            b.anschaffungswert,
            b.st_beleg_nr,
            to_char(b.anschaffungsdatum, 'YYYY-MM-DD') as anschaffungsdatum, 
            b.zubehoer,
            b.st_inventar_nr,
            b.stb_inventar_nr,
            b.konto,
            to_char(b.ausgabedatum, 'YYYY-MM-DD') as ausgabedatum, 
            to_char(b.ruecknahmedatum, 'YYYY-MM-DD') as ruecknahmedatum, 
            to_char(b.prueftermin1, 'YYYY-MM-DD') as prueftermin1, 
            to_char(b.prueftermin2, 'YYYY-MM-DD') as prueftermin2, 
            b.bemerkung,
            b.fluke_nr
        FROM oc_bdb_bestand b 
            inner join oc_bdb_kategorie k on (b.kategorie=k.id)
        ORDER BY 1;";


        $stmt = $this->dbh->prepare($sql);
        /** @var PDOStatement $stmt */
        # TODO $stmt->bindParam(':uid', $this->uid);
        # $stmt->bindValue(':wochenbeginn', date_format($this->wochenbeginn, 'Y-m-d'));
        $stmt->execute();
        while ($zeile = $stmt->fetch()) {
            $this->echoZeile($zeile);
        }

        $stmt->closeCursor();
    }

    private function echoZeile($zeile)
    {
        echo('<tr>');
        foreach ($zeile as $f) {
            echo('<td>' . $f . '</td>');
        }
        echo("</tr>\n");
    }

    public function echoSuchtext()
    {
        echo($this->suchtext);
    }
}
