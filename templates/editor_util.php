<?php

class Eintrag
{
    /** @var PDO $dbh */
    private $dbh;

    private $uid;
    private $wochenbeginn;

    private $id;

    private $feiertag = false;
    private $arbeitszeitverlagerung = false;
    private $auftragsnr = '';
    private $bauvorhaben = '';
    private $datum;
    private $von = '8:00';
    private $bis;

    private $stunden = 0.0;
    private $ueberstunden = false;
    private $lohnart = '';

    private $erschwernr = '';
    private $erschwerstunden = 0.0;
    private $erschwertaetigkeit = '';
    private $rufbereitschaft = false;
    private $verpflegungsmehraufwand = '';

    private $message = '';
    private $isBuchhalter = false;


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
        else {
            if (array_key_exists('week', $params) && 0 < strlen($params['week'])) {
                try {
                    $wb = new DateTime($params['week']);
                } catch (exception $e) {
                    $wb = new DateTime();
                } // ignore
            } else
                $wb = new DateTime();

            # Ist wochenbeginn Montag?
            $weekday = date_format($wb, 'N');
            if (1 != $weekday) {
                $i = new DateInterval('P' . ($weekday - 1) . 'D');
                $i->invert = 1;
                $wb = date_add($wb, $i);
            }
            $this->wochenbeginn = $wb;
        }

        $this->getDbh();

        if (0 < $this->id)
            if ($this->load()) {
                try {
                    $wb = new DateTime($this->datum);
                } catch (exception $e) { // ignore
                    $wb = new DateTime();
                }
                $weekday = date_format($wb, 'N');
                if (1 != $weekday) {
                    $i = new DateInterval('P' . ($weekday - 1) . 'D');
                    $i->invert = 1;
                    $wb = date_add($wb, $i);
                }
                $this->wochenbeginn = $wb;
            } else {
                $this->id = 0;
            }

        if (0 == $this->id) {
            $this->datum = date('Y-m-d');
            if (array_key_exists('mitarbeiter', $params) && (0 < strlen($params['mitarbeiter'])))
                $this->uid = $this->checkMitarbeiter($params['mitarbeiter']);
        }

        $u_uid = $user->getUID();
        $this->getPermissions($u_uid);

        if (array_key_exists('feiertag', $params)) $this->feiertag = ('1' == $params['feiertag']);
        if (array_key_exists('arbeitszeitverlagerung', $params)) $this->arbeitszeitverlagerung = ('1' == $params['arbeitszeitverlagerung']);
        if (array_key_exists('auftragsnr', $params)) $this->auftragsnr = $params['auftragsnr'];
        if (array_key_exists('bauvorhaben', $params)) $this->bauvorhaben = $params['bauvorhaben'];
        if (array_key_exists('datum', $params)) $this->datum = $params['datum'];
        if (array_key_exists('von', $params)) $this->von = $params['von'];
        if (array_key_exists('bis', $params)) $this->bis = $params['bis'];

        if (array_key_exists('stunden', $params)) $this->stunden = $params['stunden'];
        if (array_key_exists('ueberstunden', $params)) $this->ueberstunden = ('1' == $params['ueberstunden']);
        if (array_key_exists('lohnart', $params)) $this->lohnart = $params['lohnart'];

        if (array_key_exists('erschwer_nr', $params)) $this->erschwernr = $params['erschwer_nr'];
        if (array_key_exists('erschwer_stunden', $params)) $this->erschwerstunden = $params['erschwer_stunden'];
        if (array_key_exists('erschwer_taetigkeit', $params)) $this->erschwertaetigkeit = $params['erschwer_taetigkeit'];
        if (array_key_exists('rufbereitschaft', $params)) $this->rufbereitschaft = ('1' == $params['rufbereitschaft']);
        if (array_key_exists('verpflegungsmehraufwand', $params)) $this->verpflegungsmehraufwand = $params['verpflegungsmehraufwand'];

        if (array_key_exists('error_msg', $params)) $this->message = $params['error_msg'];

        if (null === $this->uid) $this->uid = $u_uid;
    }

    /**
     *
     */
    private function getDbh()
    {
        include('stundenzettel/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $this->dbh = new PDO($conn, $db_config['user'], $db_config['password']);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param String $mitarbeiter
     * @return String|null
     */
    private function checkMitarbeiter($mitarbeiter) : ?String
    {
        $back = null;
        $sql = 'SELECT uid FROM oc_zeiterf_user Where uid=:uid;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $mitarbeiter);
        $stmt->execute();
        if ($content = $stmt->fetch())
            $back = $content['uid'];
        $stmt->closeCursor();

        return $back;
    }

    /**
     * @param String $u_uid
     */
    private function getPermissions($u_uid)
    {
        $sql = 'SELECT buchhalter FROM oc_zeiterf_user Where uid=:uid;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $u_uid);
        $stmt->execute();
        if ($content = $stmt->fetch())
            $this->isBuchhalter = $content['buchhalter'];
        $stmt->closeCursor();
    }


    /**
     * @return bool
     */
    private function load() : bool
    {
        $back = false;
        $sql = "SELECT 
            e.datum, to_char(e.von, 'HH24:MI') as von, to_char(e.bis, 'HH24:MI') as bis, 
            e.feiertag, e.arbeitszeitverlagerung,
            e.auftragsnr, e.bauvorhaben, e.stunden, e.ueberstunden,
            e.lohnart, e.erschwer_stunden, e.erschwer_nr,
            e.erschwer_taetigkeit, e.rufbereitschaft,
            e.verpflegungsmehraufwand,
            u.uid 
            FROM oc_zeiterf_entry e
            inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
            inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
            WHERE e.id=:id;";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        if ($content = $stmt->fetch()) {

            $this->feiertag = $content['feiertag'];
            $this->arbeitszeitverlagerung = $content['arbeitszeitverlagerung'];
            $this->auftragsnr = $content['auftragsnr'];
            $this->bauvorhaben = $content['bauvorhaben'];
            $this->datum = $content['datum'];
            $this->von = $content['von'];

            $sql_bis = $content['bis'];
            $sql_bis = '24:00' == $sql_bis ? '00:00' : $sql_bis;
            $this->bis = $sql_bis;

            $this->stunden = $content['stunden'];
            $this->ueberstunden = $content['ueberstunden'];
            $this->lohnart = $content['lohnart'];

            $this->erschwernr = $content['erschwer_nr'];
            $this->erschwerstunden = $content['erschwer_stunden'];
            $this->erschwertaetigkeit = $content['erschwer_taetigkeit'];
            $this->rufbereitschaft = $content['rufbereitschaft'];
            $this->verpflegungsmehraufwand = $content['verpflegungsmehraufwand'];
            $this->uid = $content['uid'];
            $back = true;
        }
        $stmt->closeCursor();
        return $back;
    }


    /**
     * ermöglich das Ändern der Lohnart
     * nur für Buchhalter sichtbar
     */
    public function echoLohnartEditor()
    {
        if (!$this->isBuchhalter) return;

        echo('<tr><th>Lohnart:</th><td><input type="number" name="lohnart" id="lohnart" value="');
        echo($this->lohnart);
        echo('" /> </td></tr>');
    }

    public function echoFeiertagChecked()
    {
        echo($this->feiertag ? 'checked' : '');
    }


    public function echoArbeitszeitverlagerungChecked()
    {
        echo($this->arbeitszeitverlagerung ? 'checked' : '');
    }


    public function echoMessage()
    {
        echo($this->message);
    }

    public function echoEntryId()
    {
        echo($this->id);
    }

    public function echoUid()
    {
        echo($this->uid);
    }

    public function echoDatum()
    {
        echo($this->datum);
    }

    public function echoVon()
    {
        echo($this->von);
    }

    public function echoBis()
    {
        echo($this->bis);
    }


    public function echoAuftragsnr()
    {
        echo($this->auftragsnr);
    }

    public function echoBauvorhaben()
    {
        echo($this->bauvorhaben);
    }

    public function echoStunden()
    {
        echo($this->stunden);
    }

    public function echoUeberstundenChecked()
    {
        echo($this->ueberstunden ? 'checked' : '');
    }

    public function echoErschwerStunden()
    {
        echo($this->erschwerstunden);
    }

    public function echoErschwerNr()
    {
        echo($this->erschwernr);
    }

    public function echoErschwerTaetigkeit()
    {
        echo($this->erschwertaetigkeit);
    }

    public function echoRufbereitschaftChecked()
    {
        echo($this->rufbereitschaft ? 'checked' : '');
    }

    public function echoVerpflegungsmehraufwand()
    {
        echo($this->verpflegungsmehraufwand);
    }

    public function echoSubmitNext()
    {
        if (!$this->isBuchhalter) return;

        echo('<input type="submit" name="submit_next" id="submit_next" value="speichern und nächster" />');
    }

    /**
     *
     */
    public function echoTableLink()
    {
        $urlGenerator = \OC::$server->getURLGenerator();
        if (! is_null($this->wochenbeginn))
            $params['week'] = date_format($this->wochenbeginn, 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $urlGenerator->linkToRoute('stundenzettel.page.index', $params);

        echo('<a href="' . $absoluteUrl . '">verwerfen</a>');
    }
}

