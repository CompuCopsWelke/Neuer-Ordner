<?php

class Bestand
{
    /** @var PDO $dbh */
    private $dbh;
    private $urlGenerator;
    private $uid;

    private $message = '';
    private $editable = False;

    private $id;

    private $kategorie;
    private $kategorie_name;
    private $inventar_nr;
    private $serien_nr;
    private $weitere_nr;
    private $geheim_nr;
    private $bezeichnung;
    private $typenbezeichnung;
    private $lieferant;
    private $standort;
    private $nutzer;
    private $st_beleg_nr;
    private $zubehoer;
    private $st_inventar_nr;
    private $stb_inventar_nr;
    private $konto;
    private $bemerkung;
    private $fluke_nr;
    private $anschaffungswert;
    private $anschaffungsdatum;
    private $prueftermin1;
    private $prueftermin2;
    private $ausgabedatum;
    private $ruecknahmedatum;


    /**
     * Eintrag constructor.
     * @param $params
     * @throws Exception
     */
    public function __construct($params)
    {
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) throw new \Exception('user missing');

        if (array_key_exists('id', $params) && is_numeric($params['id']))
            $this->id = $params['id'];

        $this->getDbh();

        $this->urlGenerator = \OC::$server->getURLGenerator();

        if (0 < $this->id)
            if (!$this->load())
                $this->id = 0;

        if (0 == $this->id) {
            $this->anschaffungsdatum = date('Y-m-d');
        }
        if (array_key_exists('message', $params)) 
            $this->message = $params['message'];

        $this->uid = $user->getUID();
        $this->getPermissions();

        if (array_key_exists('inventar_nr', $params)) $this->inventar_nr = $params['inventar_nr'];
        if (array_key_exists('serien_nr', $params)) $this->serien_nr = $params['serien_nr'];
        if (array_key_exists('weitere_nr', $params)) $this->weitere_nr = $params['weitere_nr'];
        if (array_key_exists('geheim_nr', $params)) $this->geheim_nr = $params['geheim_nr'];
        if (array_key_exists('bezeichnung', $params)) $this->bezeichnung = $params['bezeichnung'];
        if (array_key_exists('typenbezeichnung', $params)) $this->typenbezeichnung = $params['typenbezeichnung'];
        if (array_key_exists('lieferant', $params)) $this->lieferant = $params['lieferant'];
        if (array_key_exists('standort', $params)) $this->standort = $params['standort'];
        if (array_key_exists('nutzer', $params)) $this->nutzer = $params['nutzer'];
        if (array_key_exists('st_beleg_nr', $params)) $this->st_beleg_nr = $params['st_beleg_nr'];
        if (array_key_exists('zubehoer', $params)) $this->zubehoer = $params['zubehoer'];
        if (array_key_exists('st_inventar_nr', $params)) $this->st_inventar_nr = $params['st_inventar_nr'];
        if (array_key_exists('stb_inventar_nr', $params)) $this->stb_inventar_nr = $params['stb_inventar_nr'];
        if (array_key_exists('konto', $params)) $this->konto = $params['konto'];
        if (array_key_exists('bemerkung', $params)) $this->bemerkung = $params['bemerkung'];
        if (array_key_exists('fluke_nr', $params)) $this->fluke_nr = $params['fluke_nr'];
        if (array_key_exists('anschaffungswert', $params)) $this->anschaffungswert = $params['anschaffungswert'];
        if (array_key_exists('anschaffungsdatum', $params)) $this->anschaffungsdatum = $params['anschaffungsdatum'];
        if (array_key_exists('prueftermin1', $params)) $this->prueftermin1 = $params['prueftermin1'];
        if (array_key_exists('prueftermin2', $params)) $this->prueftermin2 = $params['prueftermin2'];
        if (array_key_exists('ausgabedatum', $params)) $this->ausgabedatum = $params['ausgabedatum'];
        if (array_key_exists('ruecknahmedatum', $params)) $this->ruecknahmedatum = $params['ruecknahmedatum'];
    }

    /**
     *
     */
    private function getDbh()
    {
        include('bestand/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $this->dbh = new PDO($conn, $db_config['user'], $db_config['password']);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->st_adr = $ST_ADR;
    }

    /**
     * @return bool
     */
    private function load(): bool
    {
        $back = false;
        $sql = 'SELECT b.*, k.name as kategorie_name 
            FROM oc_bdb_bestand b 
            WHERE b.id=:id;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        if ($content = $stmt->fetch()) {
            $this->id = $content['id'];

            $this->kategorie = $content['kategorie'];
            $this->kategorie_name = $content['kategorie_name'];

            $this->inventar_nr = $content['inventar_nr'];
            $this->serien_nr = $content['serien_nr'];
            $this->weitere_nr = $content['weitere_nr'];
            $this->geheim_nr = $content['geheim_nr'];
            $this->bezeichnung = $content['bezeichnung'];
            $this->typenbezeichnung = $content['typenbezeichnung'];
            $this->lieferant = $content['lieferant'];
            $this->standort = $content['standort'];
            $this->nutzer = $content['nutzer'];
            $this->st_beleg_nr = $content['st_beleg_nr'];
            $this->zubehoer = $content['zubehoer'];
            $this->st_inventar_nr = $content['st_inventar_nr'];
            $this->stb_inventar_nr = $content['stb_inventar_nr'];
            $this->konto = $content['konto'];
            $this->bemerkung = $content['bemerkung'];
            $this->fluke_nr = $content['fluke_nr'];
            $this->anschaffungswert = $content['anschaffungswert'];
            $this->anschaffungsdatum = $content['anschaffungsdatum'];
            $this->prueftermin1 = $content['prueftermin1'];
            $this->prueftermin2 = $content['prueftermin2'];
            $this->ausgabedatum = $content['ausgabedatum'];
            $this->ruecknahmedatum = $content['ruecknahmedatum'];

            $back = true;
        }
        $stmt->closeCursor();

        return $back;
    }

    private function getPermissions() # TODO , $kategorie
    {
        $this->editable = false;
        $sql = 'SELECT id
            FROM oc_bdb_kategorie_perm
            WHERE uid=:uid 
            Limit 1;';
              # kategorie=:kategorie TODO

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $this->uid);
        $stmt->execute();
        if ($content = $stmt->fetch())
            $this->editable = true;
        
        $stmt->closeCursor();
    }

    public function echoMessage()
    {
        echo($this->message);
    }

    public function isEditable()
    {
        return $this->editable;
    }


    public function getBestandId()
    {
        return $this->id;
    }

    public function echoBestandId()
    {
        echo($this->id);
    }

    public function echoKategorie()
    {
        echo('<td colspan="2">');
        if ($this->editable) {
            $this->selectKategorie();
        } else {
            echo($this->kategorie_name);
        }
        echo('</td>');
    }

    public function selectKategorie()
    {
        echo('<select name="kategorie">');
        $sql = 'SELECT k.id, k.name 
            FROM oc_bdb_kategorie k inner join oc_bdb_kategorie_perm p on (k.id=p.kategorie)
            WHERE p.uid=:uid 
            ORDER BY 2;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $this->uid);
        $stmt->execute();

        while ($content = $stmt->fetch()) {
            $s = htmlspecialchars($content['name']);
            echo('<option value="'.$content['id'].'">' . $s . '</option>');
        }
        $stmt->closeCursor();
        echo('</select>');
    }

    private function echoTextField($name, $value, $length) {
        if ($this->editable) {
            echo('<td colspan="2"><input type="text" name="'.$name.'" id="'.$name.'" maxlength="'.$length.'" value="');
            echo($value);
            echo('" /> </td>');
        } else {
            echo('<td colspan="2">');
            echo($value);
            echo('</td><td></td>');
        }
    }

    private function echoTextArea($name, $value, $length) {
        if ($this->editable) {
            echo('<td colspan="2"><textarea rows="3" maxlength="'.$length.'" name="'.$name.'" id="'.$name.'" style="width:500px">');
            echo($value);
            echo('</textarea></td>');
        } else {
            echo('<td colspan="2">');
            echo($value);
            echo('</td><td>');
        }
    }

    private function echoDatum($name, $value) {
        if ($this->editable) {
            echo('<td><input type="date" name="'.$name.'" id="'.$name.'" value="');
            echo($value);
            echo('" /> </td>');
        } else {
            echo('<td colspan="2">');
            echo($value);
            echo('</td><td>');
        }
    }

    public function echoInventar_nr()
    {
        $this->echoTextField('inventar_nr', $this->inventar_nr, 50);
    }

    public function echoSerien_nr()
    {
        $this->echoTextField('serien_nr', $this->serien_nr, 50);
    }

    public function echoWeitere_nr()
    {
        $this->echoTextField('weitere_nr', $this->weitere_nr, 50);
    }

    public function echoGeheim_nr()
    {
        $this->echoTextField('geheim_nr', $this->geheim_nr, 50);
    }

    public function echoBezeichnung()
    {
        $this->echoTextField('bezeichnung', $this->bezeichnung, 100);
    }

    public function echoTypenbezeichnung()
    {
        $this->echoTextField('typenbezeichnung', $this->typenbezeichnung, 100);
    }

    public function echoLieferant()
    {
        $this->echoTextField('lieferant', $this->lieferant, 100);

    }

    public function echoStandort()
    {
        $this->echoTextField('standort', $this->standort, 100);
    }

    public function echoNutzer()
    {
        $this->echoTextField('nutzer', $this->nutzer, 100);
    }

    public function echoSt_beleg_nr()
    {
        $this->echoTextField('st_beleg_nr', $this->st_beleg_nr, 50);
    }

    public function echoZubehoer()
    {
        $this->echoTextArea('zubehoer', $this->zubehoer, 1000);
    }

    public function echoSt_inventar_nr()
    {
        $this->echoTextField('st_inventar_nr', $this->st_inventar_nr, 50);
    }

    public function echoStb_inventar_nr()
    {
        $this->echoTextField('stb_inventar_nr', $this->stb_inventar_nr, 50);
    }

    public function echoKonto()
    {
        $this->echoTextField('konto', $this->konto, 50);
    }

    public function echoFluke_nr()
    {
        $this->echoTextField('fluke_nr', $this->fluke_nr, 50);
    }

    public function echoBemerkung()
    {
        $this->echoTextArea('bemerkung', $this->bemerkung, 10000);
    }

    public function echoAnschaffungswert()
    {
        if ($this->editable) {
            echo('<td><input type="number" name="anschaffungswert" id="anschaffungswert" value="');
            echo($this->anschaffungswert);
            echo('" /> </td>');
        } else {
            echo('<td>');
            echo($this->anschaffungswert);
            echo('</td>');
        }
    }


    public function echoAnschaffungsdatum()
    {
        $this->echoDatum('anschaffungsdatum', $this->anschaffungsdatum);
    }

    public function echoPrueftermin1()
    {
        $this->echoDatum('prueftermin1', $this->prueftermin1);
    }

    public function echoPrueftermin2()
    {
        $this->echoDatum('prueftermin2', $this->prueftermin2);
    }

    public function echoAusgabedatum()
    {
        $this->echoDatum('ausgabedatum', $this->ausgabedatum);
    }

    public function echoRuecknahmedatum()
    {
        $this->echoDatum('ruecknahmedatum', $this->ruecknahmedatum);
    }

    public function echoDocTable()
    {
        $sql = 'SELECT id, titel, dateiname FROM oc_bdb_doc WHERE bestand=:id;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute())
            while ($content = $stmt->fetch()) {
                $params['doc_id'] = $content['id'];
                $show_url = $this->urlGenerator->linkToRoute('bestand.bestand.show_doc', $params);
                $del_url = $this->urlGenerator->linkToRoute('bestand.bestand.del_doc', $params);

                echo('<tr>');
                echo('<td><a href="' . $show_url . '">' . htmlspecialchars($content['titel']) . '</a></td>');
                echo('<td><a href="' . $show_url . '">' . htmlspecialchars($content['dateiname']) . '</a></td>');
                echo('<td><a href="' . $del_url . '"><svg width="20" height="20" viewBox="0 0 20 20" alt="Loeschen">
                        <image x="0" y="0" width="20" height="20" preserveAspectRatio="xMinYMin meet" xlink:href="/apps/bestand/img/delete.svg"  class="app-icon"></image></svg></a></td>');
                echo('</tr>');
            }
        $stmt->closeCursor();
    }

    public function echoCreateBestand()
    {
        $params['kategorie'] = $this->kategorie;
        $absoluteUrl = $this->urlGenerator->linkToRoute('bestand.editor.create', $params);

        echo('<p><a href="' . $absoluteUrl . '">neu</a>');
    }
}
