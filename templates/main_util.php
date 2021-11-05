<?php


class Bestandliste 
{
    /** @var PDO $dbh */
    private $dbh;
    private $urlGenerator;

    private $message;

    private $kategorie;
    private $suchfeld;
    private $suchtext;
    private $datumfeld;
    private $von;
    private $bis;

    private const SuchFeldList = [
        ['inventar_nr', 'Inventar-Nr'], 
        ['serien_nr', 'Serien-Nr'],
        ['weitere_nr', 'weitere nr'],
        ['geheim_nr', 'geheim_nr'],
        ['bezeichnung', 'Bezeichnung'],
        ['typenbezeichnung', 'Typenbezeichnung'],
        ['lieferant', 'Lieferant'],
        ['standort', 'Standort'],
        ['nutzer', 'Nutzer'],
        ['st_beleg_nr', 'ST-Beleg-Nr'],
        ['zubehoer', 'Zubehör'],
        ['st_inventar_nr', 'ST-Inventar-Nr'],
        ['stb_inventar_nr', 'STB-Inventar-Nr'],
        ['konto', 'Konto'],
        ['bemerkung', 'Bemerkung'],
        ['fluke_nr', 'Fluke-Nr']
    ];

    private const DatumFeldList = [
        ['anschaffungsdatum', 'Anschaffungsdatum'],
        ['ausgabedatum', 'Ausgabedatum'],
        ['ruecknahmedatum', 'Rücknahmedatum'],
        ['prueftermin1', 'Prüftermin1'],
        ['prueftermin2', 'Prüftermin2']
    ];


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
            $id = $content['id'];
            $s = '<option value="'.$id.'"';
            if ($this->kategorie == $id) $s .= ' selected';

            $n = htmlspecialchars($content['name']);

            echo($s .'>' . $n . '</option>');
        }
        $stmt->closeCursor();
        echo('</select>');
    }

    public function selectSuchfeld()
    {
        echo('<select name="suchfeld">');

        echo('<option value="">&lt;Standard&gt;</option>');
        foreach (Bestandliste::SuchFeldList as $f) {
            $s = '<option value="' . $f[0] . '"';
            if ($this->suchfeld == $f[0]) $s .= ' selected';

            echo($s. '>' . $f[1] . "</option>\n");
        }
        echo('</select>');
    }


    public function selectDatumfeld()
    {
        echo('<select name="datumfeld">');

        echo('<option value="">&lt;bitte auswählen&gt;</option>');
        foreach (Bestandliste::DatumFeldList as $f) {
            $s = '<option value="' . $f[0] . '"';
            if ($this->datumfeld == $f[0]) $s .= ' selected';

            echo($s. '>' . $f[1] . "</option>\n");
        }
        echo('</select>');
    }

    public function echoVon()
    {
        echo($this->von);
    }

    public function echoBis()
    {
        echo($this->bis);
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
        WHERE true";
        
        if (0 < strlen($this->kategorie)) $sql .= ' and (b.kategorie = :kategorie)';
        $sql .= $this->addSuchFeld();
        $sql .= $this->addDatumFeld();

        $sql .= ' ORDER BY 1;';

        $stmt = $this->dbh->prepare($sql);
        if (0 < strlen($this->kategorie)) $stmt->bindParam(':kategorie', $this->kategorie);
        if (0 < strlen($this->suchtext)) $stmt->bindParam(':suchtext', $this->suchtext);
        if (0 < strlen($this->von)) $stmt->bindParam(':von', $this->von);
        if (0 < strlen($this->bis)) $stmt->bindParam(':bis', $this->bis);

        # $stmt->bindValue(':wochenbeginn', date_format($this->wochenbeginn, 'Y-m-d'));
        $stmt->execute();
        while ($zeile = $stmt->fetch()) {
            $this->echoZeile($zeile);
        }

        $stmt->closeCursor();
    }

    private function addSuchFeld()
    {
        
        if (0 >= strlen($this->suchtext))
            return '';

        foreach (Bestandliste::SuchFeldList as $f)
            if ($this->suchfeld == $f[0]) 
                return " and ".$f[0] . ' = :suchtext ';

        return '';
    }

    private function addDatumFeld()
    {
        $suchfeld = '';
        foreach (Bestandliste::DatumFeldList as $f)
            if ($this->datumfeld == $f[0]) {
                $suchfeld = $f[0];
                break;
            }

        if (0 >= strlen($suchfeld))
            return '';


        if (0 < strlen($this->von)) {
            if (0 < strlen($this->bis)) {
                return 'and (('.$suchfeld.'>= :von) and ('.$suchfeld.'<=:bis))';
            } else
                return 'and ('.$suchfeld.'>= :von)';
        } else
            if (0 < strlen($this->bis)) 
                return 'and ('.$suchfeld.'<=:bis)';
            else
                return '';
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
