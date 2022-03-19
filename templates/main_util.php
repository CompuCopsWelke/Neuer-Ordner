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
    private $sort;

    private const SuchFeldList = [
        ['inventar_nr', 'Inventar-Nr'],
        ['serien_nr', 'Serien-Nr'],
        ['weitere_nr', 'weitere Nr'],
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

    private const SortierungList = [
        ['standard', 'Standard'],
        ['datum', 'Datum']
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

        $this->sort = array_key_exists( 'sort', $post_arr) ? $post_arr['sort'] : '';

        $this->message = array_key_exists( 'message', $post_arr) ? $post_arr['message'] : '';

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
        echo('<select name="kategorie" id="kategorie">');
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

    public function selectSortierung()
    {
        echo('<select name="sort">');

        foreach (Bestandliste::SortierungList as $f) {
            $s = '<option value="' . $f[0] . '"';
            if ($this->sort == $f[0]) $s .= ' selected';

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
        $sql = "SELECT b.id,
            k.name as kategorie_name,
            b.inventar_nr,
            b.serien_nr,
            b.weitere_nr,
            b.bezeichnung,
            b.typenbezeichnung,
            b.lieferant,
            b.standort,
            b.nutzer,
            b.anschaffungswert,
            b.st_beleg_nr,
            to_char(b.anschaffungsdatum, 'DD.MM.YYYY') as anschaffungsdatum_s,
            b.zubehoer,
            b.st_inventar_nr,
            b.stb_inventar_nr,
            b.konto,
            to_char(b.ausgabedatum, 'DD.MM.YYYY') as ausgabedatum_s,
            to_char(b.ruecknahmedatum, 'DD.MM.YYYY') as ruecknahmedatum_s,
            to_char(b.prueftermin1, 'DD.MM.YYYY') as prueftermin1_s,
            to_char(b.prueftermin2, 'DD.MM.YYYY') as prueftermin2_s,
            substring(b.bemerkung for 50) as bemerkung,
            b.fluke_nr,
            CASE WHEN now() >= b.prueftermin1 THEN 'red'
                WHEN (now() + interval '30 days') >= b.prueftermin1 THEN 'yellow'
                ELSE ''
            END as prueftermin1_class,
            CASE WHEN now() >= b.prueftermin2 THEN 'red'
                WHEN (now() + interval '30 days') >= b.prueftermin2 THEN 'yellow'
                ELSE ''
            END as prueftermin2_class
        FROM oc_bdb_bestand b
            inner join oc_bdb_kategorie k on (b.kategorie=k.id)
        WHERE true";

        if (0 < strlen($this->kategorie)) $sql .= ' and (b.kategorie = :kategorie)';
        $sql .= $this->addSuchFeld();
        $sql .= $this->addDatumFeld();

        $sql .= $this->addOrderBy();

        $stmt = $this->dbh->prepare($sql);
        if (0 < strlen($this->kategorie)) $stmt->bindParam(':kategorie', $this->kategorie);
        if (0 < strlen($this->suchtext)) $stmt->bindParam(':suchtext', $this->suchtext);
        if (0 < strlen($this->von)) $stmt->bindParam(':von', $this->von);
        if (0 < strlen($this->bis)) $stmt->bindParam(':bis', $this->bis);
        $stmt->execute();
        while ($zeile = $stmt->fetch())
            $this->echoZeile($zeile);

        $stmt->closeCursor();
    }

    private function addSuchFeld()
    {
        if (0 >= strlen($this->suchtext))
            return '';

        if ('' == $this->suchfeld)
            return " and ((b.inventar_nr ilike :suchtext)
                        or (b.serien_nr ilike :suchtext)
                        or (b.weitere_nr ilike :suchtext)
                        or (b.bezeichnung ilike :suchtext)
                        or (b.typenbezeichnung ilike :suchtext)
                        )";

        foreach (Bestandliste::SuchFeldList as $f)
            if ($this->suchfeld == $f[0])
                return " and ".$f[0] . ' ilike :suchtext ';

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
                return ' and (('.$suchfeld.'>= :von) and ('.$suchfeld.'<=:bis))';
            } else
                return ' and ('.$suchfeld.'>= :von)';
        } else
            if (0 < strlen($this->bis))
                return ' and ('.$suchfeld.'<=:bis)';
            else
                return '';
    }


    private function addOrderBy()
    {
        $selected_sort = '';
        foreach (Bestandliste::SortierungList as $f)
            if ($this->sort == $f[0]) {
                $selected_sort = $f[0];
                break;
            }

        if ( "datum" == $selected_sort)
            foreach (Bestandliste::DatumFeldList as $f)
                if ($this->datumfeld == $f[0])
                    return ' Order by '.$f[0].',k.name, b.bezeichnung, b.typenbezeichnung, b.inventar_nr, b.serien_nr;';

        # Standard oder falscher Parameter
        return ' Order by k.name, b.bezeichnung, b.typenbezeichnung, b.inventar_nr, b.serien_nr;';
    }

    private function getPruefTerminColor($termin_class)
    {
        if (0 < strlen($termin_class))
            return ' style="background-color: '.$termin_class.';"';

        return '';
    }

    private function echoZeile($zeile)
    {
        $params['id'] = $zeile['id'];
        $params['letzte_kategorie'] = $this->kategorie;
        $edit_url = $this->urlGenerator->linkToRoute('bestand.editor.edit', $params);

        echo('<tr>');
        echo('<td>' . $zeile['kategorie_name'] . '</td>');
        echo('<td><a href="' . $edit_url . '"/>' . htmlspecialchars($zeile['inventar_nr']) . '</td>');
        echo('<td><a href="' . $edit_url . '"/>' . htmlspecialchars($zeile['serien_nr']) . '</td>');
        echo('<td><a href="' . $edit_url . '"/>' . htmlspecialchars($zeile['weitere_nr']) . '</td>');
        echo('<td><a href="' . $edit_url . '"/>' . htmlspecialchars($zeile['bezeichnung']) . '</td>');
        echo('<td><a href="' . $edit_url . '"/>' . htmlspecialchars($zeile['typenbezeichnung']) . '</td>');
        echo('<td>' . $zeile['lieferant'] . '</td>');
        echo('<td>' . $zeile['standort'] . '</td>');
        echo('<td>' . $zeile['nutzer'] . '</td>');
        echo('<td>' . $zeile['anschaffungswert'] . '</td>');
        echo('<td>' . $zeile['st_beleg_nr'] . '</td>');
        echo('<td>' . $zeile['anschaffungsdatum_s'] . '</td>');
        echo('<td>' . $zeile['zubehoer'] . '</td>');
        echo('<td>' . $zeile['st_inventar_nr'] . '</td>');
        echo('<td>' . $zeile['stb_inventar_nr'] . '</td>');
        echo('<td>' . $zeile['konto'] . '</td>');
        echo('<td>' . $zeile['ausgabedatum_s'] . '</td>');
        echo('<td>' . $zeile['ruecknahmedatum_s'] . '</td>');

        $color = $this->getPruefTerminColor($zeile['prueftermin1_class']);
        echo('<td'. $color .'>' . $zeile['prueftermin1_s'] . '</td>');
        $color = $this->getPruefTerminColor($zeile['prueftermin2_class']);
        echo('<td'. $color .'>' . $zeile['prueftermin2_s'] . '</td>');
        echo('<td>' . $zeile['bemerkung'] . '</td>');
        echo('<td>' . $zeile['fluke_nr'] . '</td>');

        echo("</tr>\n");
    }


    public function echoSuchtext()
    {
        echo($this->suchtext);
    }
}
