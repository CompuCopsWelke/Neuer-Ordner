<?php


class Bestandliste
{
    /** @var PDO $dbh */
    private $dbh;
    private $urlGenerator;

    private $uid;

    private $message;

    private $kategorie;
    private $suchfeld;
    private $suchtext;
    private $datumfeld;
    private $von;
    private $bis;

    private $sort;
    private $sort_col;
    private $sort_direct;

    private const SuchFeldList = [
        ['inventar_nr', 'Inventar-Nr'],
        ['serien_nr', 'Serien-Nr'],
        ['weitere_nr', 'weitere Nr'],
        ['bezeichnung', 'Bezeichnung'],
        ['typenbezeichnung', 'Typenbezeichnung'],
        ['lieferant', 'Lieferant'],
        ['standort', 'Standort'],
        ['nutzer', 'Nutzer'],
        ['einsatzort', 'Einsatzort'],
        ['st_beleg_nr', 'ST-Beleg-Nr'],
        ['zubehoer', 'Zubehör'],
        ['st_inventar_nr', 'ST-Inventar-Nr'],
        ['stb_inventar_nr', 'STB-Inventar-Nr'],
        ['konto', 'Konto'],
        ['bemerkung', 'Bemerkung'],
        ['fluke_nr', 'Fluke-Nr'],
    ];

    private const DatumFeldList = [
        ['anschaffungsdatum', 'Anschaffungsdatum'],
        ['ausgabedatum', 'Ausgabedatum'],
        ['ruecknahmedatum', 'Rücknahmedatum'],
        ['prueftermin1', 'Prüftermin1'],
        ['prueftermin2', 'Prüftermin2'],
    ];

    private const SortierungList = [
        ['standard', 'Standard'],
        ['datum', 'Datum'],
    ];

    private const SortierungColumn = [
        ['kategorie_name', 'Kategorie'],
        ['inventar_nr', 'Inventar-Nr'],
        ['serien_nr', 'Serien-Nr'],
        ['weitere_nr', 'Weitere Nr'],
        ['bezeichnung', 'Bezeichnung'],
        ['typenbezeichnung', 'Typenbezeichnung'],
        ['lieferant', 'Lieferant'],
        ['standort', 'Standort'],
        ['nutzer', 'Nutzer'],
        ['einsatzort', 'Einsatzort'],
        ['anschaffungswert', 'Anschaffungswert'],
        ['st_beleg_nr', 'ST-Beleg-nr'],
        ['anschaffungsdatum', 'Anschaffungsdatum'],
        ['zubehoer', 'Zubehör'],
        ['st_inventar_nr', 'St-inventar-nr'],
        ['stb_inventar_nr', 'StB-Inventar-Nr'],
        ['konto', 'Konto'],
        ['ausgabedatum', 'Ausgabedatum'],
        ['ruecknahmedatum', 'Rücknahmedatum'],
        ['prueftermin1', 'Prüftermin1'],
        ['prueftermin2', 'Prüftermin2'],
        ['bemerkung', 'Bemerkung'],
        ['fluke_nr', 'Fluke-Nr'],
    ];


    /**
     * Bestandliste constructor.
     * @throws Exception
     */
    public function __construct($post_arr)
    {
        $this->kategorie = array_key_exists('kategorie', $post_arr) ? $post_arr['kategorie'] : '';
        $this->suchfeld = array_key_exists('suchfeld', $post_arr) ? $post_arr['suchfeld'] : '';
        $this->suchtext = array_key_exists('suchtext', $post_arr) ? $post_arr['suchtext'] : '';

        $this->datumfeld = array_key_exists('datumfeld', $post_arr) ? $post_arr['datumfeld'] : '';
        $this->von = array_key_exists('von', $post_arr) ? $post_arr['von'] : '';
        $this->bis = array_key_exists('bis', $post_arr) ? $post_arr['bis'] : '';

        $this->sort = array_key_exists('sort', $post_arr) ? $post_arr['sort'] : '';
        $this->sort_col = array_key_exists('sort_col', $post_arr) ? $post_arr['sort_col'] : '';
        $this->sort_direct = array_key_exists('sort_direct', $post_arr) ? $post_arr['sort_direct'] : '';

        $this->message = array_key_exists('message', $post_arr) ? $post_arr['message'] : '';

        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $this->message = 'no login ????';
            return;
        }
        $this->uid = $user->getUID();

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
        echo('<b>' . $this->message . '</b>');
    }

    public function echoGotoIndex()
    {
        echo($this->urlGenerator->linkToRoute('bestand.page.index', []));
    }

    public function echoCreateBestand()
    {
        $params['letzte_kategorie'] = $this->kategorie;
        $absoluteUrl = $this->urlGenerator->linkToRoute('bestand.editor.create', $params);

        echo('<p><a href="' . $absoluteUrl . '">neu</a>');
    }


    public function selectKategorie()
    {
        echo('<select name="kategorie" id="kategorie">');
        echo('<option value="">&lt;alle&gt;</option>');

        $sql = 'SELECT k.id, name FROM oc_bdb_kategorie k
            inner join oc_bdb_kategorie_perm p on (p.kategorie=k.id)
            where (p.write or p.read) and p.uid =:uid
            ORDER BY 2;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $this->uid);
        $stmt->execute();

        while ($content = $stmt->fetch()) {
            $id = $content['id'];
            $s = '<option value="' . $id . '"';
            if ($this->kategorie == $id) $s .= ' selected';

            $n = htmlspecialchars($content['name']);

            echo($s . '>' . $n . '</option>');
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

            echo($s . '>' . $f[1] . "</option>\n");
        }
        echo('</select>');
    }


    public function selectDatumfeld()
    {
        echo('<select name="datumfeld" id="datumfeld">');

        echo('<option value="">&lt;bitte auswählen&gt;</option>');
        foreach (Bestandliste::DatumFeldList as $f) {
            $s = '<option value="' . $f[0] . '"';
            if ($this->datumfeld == $f[0]) $s .= ' selected';

            echo($s . '>' . $f[1] . "</option>\n");
        }
        echo('</select>');
    }

    public function selectSortierung()
    {
        echo('<select name="sort" id="sort">');

        foreach (Bestandliste::SortierungList as $f) {
            $s = '<option value="' . $f[0] . '"';
            if ($this->sort == $f[0]) $s .= ' selected';

            echo($s . '>' . $f[1] . "</option>\n");
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
            b.einsatzort,
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
            inner join oc_bdb_kategorie_perm p on (b.kategorie=p.kategorie)
        WHERE (p.write or p.read) and p.uid = :uid ";

        if (0 < strlen($this->kategorie)) $sql .= ' and (b.kategorie = :kategorie)';
        $sql .= $this->addSuchFeld();
        $sql .= $this->addDatumFeld();

        $sql .= $this->addOrderBy();

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $this->uid);
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
                return " and " . $f[0] . ' ilike :suchtext ';

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
                return ' and ((' . $suchfeld . '>= :von) and (' . $suchfeld . '<=:bis))';
            } else
                return ' and (' . $suchfeld . '>= :von)';
        } elseif (0 < strlen($this->bis))
            return ' and (' . $suchfeld . '<=:bis)';
        else
            return '';
    }


    private function addOrderBy()
    {
        $order = '';
        if ('' != $this->sort_col) {
            foreach (Bestandliste::SortierungColumn as $f)
                if ($this->sort_col == $f[0]) {
                    $order = $f[0];
                    break;
                }
        }
        if (0 < strlen($order)) {
            if ('desc' == $this->sort_direct)
                return 'Order by ' . $order . ' DESC;';

            return 'Order by ' . $order . ';';
        }

        $selected_sort = '';
        foreach (Bestandliste::SortierungList as $f)
            if ($this->sort == $f[0]) {
                $selected_sort = $f[0];
                break;
            }

        if ("datum" == $selected_sort)
            foreach (Bestandliste::DatumFeldList as $f)
                if ($this->datumfeld == $f[0])
                    return ' Order by ' . $f[0] . ',k.name, b.bezeichnung, b.typenbezeichnung, b.inventar_nr, b.serien_nr;';

        # Standard oder falscher Parameter
        return ' Order by k.name, b.bezeichnung, b.typenbezeichnung, b.inventar_nr, b.serien_nr;';
    }

    private function getPruefTerminColor($termin_class)
    {
        if (0 < strlen($termin_class))
            return ' style="background-color: ' . $termin_class . ';"';

        return '';
    }

    private function translateLineBreak2HTML($content)
    {
        return str_replace("\n", '<br>', $content);
    }

    private function echoZeile($zeile)
    {
        $params['id'] = $zeile['id'];
        $params['letzte_kategorie'] = $this->kategorie;
        $edit_url = $this->urlGenerator->linkToRoute('bestand.editor.edit', $params);

        echo('<tr>');
        echo('<td>' . $zeile['kategorie_name'] . '</td>');
        echo('<td><a href="' . $edit_url . '"><div>' . htmlspecialchars($zeile['inventar_nr'] ?? '') . '</div></a></td>');
        echo('<td><a href="' . $edit_url . '">' . htmlspecialchars($zeile['serien_nr'] ?? '') . '</a></td>');
        echo('<td><a href="' . $edit_url . '">' . htmlspecialchars($zeile['weitere_nr'] ?? '') . '</a></td>');
        echo('<td><a href="' . $edit_url . '">' . htmlspecialchars($zeile['bezeichnung'] ?? '') . '</a></td>');
        echo('<td><a href="' . $edit_url . '">' . htmlspecialchars($zeile['typenbezeichnung'] ?? '') . '</a></td>');
        echo('<td>' . htmlspecialchars($zeile['lieferant'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['standort'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['nutzer'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['einsatzort'] ?? '') . '</td>');
        echo('<td>' . $zeile['anschaffungswert'] . '</td>');
        echo('<td>' . htmlspecialchars($zeile['st_beleg_nr'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['anschaffungsdatum_s'] ?? '') . '</td>');
        echo('<td><div class="bestand_fix_row">' . $this->translateLineBreak2HTML(htmlspecialchars($zeile['zubehoer'] ?? '')) . '</div></td>');
        echo('<td>' . htmlspecialchars($zeile['st_inventar_nr'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['stb_inventar_nr'] ?? '') . '</td>');
        echo('<td>' . htmlspecialchars($zeile['konto'] ?? '') . '</td>');
        echo('<td>' . $zeile['ausgabedatum_s'] . '</td>');
        echo('<td>' . $zeile['ruecknahmedatum_s'] . '</td>');

        $color = $this->getPruefTerminColor($zeile['prueftermin1_class']);
        echo('<td' . $color . '>' . $zeile['prueftermin1_s'] . '</td>');
        $color = $this->getPruefTerminColor($zeile['prueftermin2_class']);
        echo('<td' . $color . '>' . $zeile['prueftermin2_s'] . '</td>');
        echo('<td><div class="bestand_fix_row">' . $this->translateLineBreak2HTML(htmlspecialchars($zeile['bemerkung'] ?? '')) . '</div></td>');
        echo('<td>' . htmlspecialchars($zeile['fluke_nr'] ?? '') . '</td>');

        echo("</tr>\n");
    }


    public function echoSuchtext()
    {
        echo($this->suchtext);
    }

    public function showTableHeader()
    {
        if (0 < strlen($this->kategorie)) $params['kategorie'] = $this->kategorie;
        if (0 < strlen($this->suchfeld)) $params['suchfeld'] = $this->suchfeld;
        if (0 < strlen($this->suchtext)) $params['suchtext'] = $this->suchtext;

        if (0 < strlen($this->datumfeld)) $params['datumfeld'] = $this->datumfeld;
        if (0 < strlen($this->von)) $params['von'] = $this->von;
        if (0 < strlen($this->bis)) $params['bis'] = $this->bis;

        if (0 < strlen($this->sort)) $params['sort'] = $this->sort;

        $header = '';
        foreach (Bestandliste::SortierungColumn as $f) {
            $params['sort_col'] = $f[0];
            if ($f[0] == $this->sort_col && ('desc' != $this->sort_direct)) {
                $params['sort_direct'] = 'desc';
                $symbol_up = ' &#8593';
            } else {
                $params['sort_direct'] = '';
                $symbol_up = '';
            }
            $sort_url = $this->urlGenerator->linkToRoute('bestand.page.index', $params);

            $header .= '<th><a href="' . $sort_url . '">' . htmlspecialchars($f[1]) . $symbol_up . '</a></th>';
        }

        echo($header);
    }
}
