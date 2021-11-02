<?php


class Wochenblatt
{
    /** @var PDO $dbh */
    private $dbh;
    private $urlGenerator;

    private $uid;
    private $wochenbeginn;
    private $wochenblatt_id;

    private $message;
    private $pruef_filter;

    private $vorgesetzter = false;
    private $buchhalter = false;


    /**
     * Wochenblatt constructor.
     * @param $wochenbeginn
     * @param $mitarbeiter
     * @throws Exception
     */
    public function __construct($post_arr)
    {
        $wochenbeginn = array_key_exists('week', $post_arr ) ? $post_arr['week'] : '';
        $mitarbeiter = array_key_exists( 'mitarbeiter', $post_arr) ? $post_arr['mitarbeiter'] : '';

        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $this->message = 'no login ????';
            return;
        }

        $this->message = array_key_exists( 'message', $post_arr) ? $post_arr['message'] : '';
        $this->pruef_filter = array_key_exists( 'pruef_filter', $post_arr) ? $post_arr['pruef_filter'] : '';

        if (0 < strlen($wochenbeginn))
            try {
                $wb = new DateTime($wochenbeginn);
            } catch (exception $e) {
                $wb = new DateTime();
            } // ignore
        else
            $wb = new DateTime();

        # Ist wochenbeginn Montag?
        $weekday = date_format($wb, 'N');
        if (1 != $weekday) {
            $i = new DateInterval('P' . ($weekday - 1) . 'D');
            $i->invert = 1;
            $wb = date_add($wb, $i);
        }
        $this->wochenbeginn = $wb;

        $this->getDbh();

        if (0 < strlen($mitarbeiter))
            $this->uid = $this->checkMitarbeiter($mitarbeiter);

        $u_uid = $user->getUID();
        $this->getPermissions($u_uid);

        if (null === $this->uid) $this->uid = $u_uid;

        $this->urlGenerator = \OC::$server->getURLGenerator();
        setlocale(LC_TIME,'de_DE.utf8'); 
    }

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
        if ($content = $stmt->fetch()) {
            $back = $content['uid'];
        }
        $stmt->closeCursor();

        return $back;
    }

    /**
     * @param String $uid
     */
    private function getPermissions($uid)
    {
        $sql = 'SELECT vorgesetzter, buchhalter FROM oc_zeiterf_user Where uid=:uid;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();
        if ($content = $stmt->fetch()) {
            $this->vorgesetzter = $content['vorgesetzter'];
            $this->buchhalter = $content['buchhalter'];
        }
        $stmt->closeCursor();
    }

    public function echoGotoWeek()
    {
        echo($this->urlGenerator->linkToRoute('stundenzettel.page.index', []));
    }

    public function echoPruefung()
    {
        echo($this->urlGenerator->linkToRoute('stundenzettel.page.pruefung', []));
    }
    
    public function echoLastEdit()
    {
        $sql = 'SELECT max(ts) as max_ts 
            FROM (
                Select max(update_ts) as ts
                FROM oc_zeiterf_entry e 
                    inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
                    inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
                WHERE u.uid=:uid and w.wochenbeginn=:wochenbeginn
                UNION
                Select max(delete_ts)
                FROM oc_zeiterf_entry_history e 
                    inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
                    inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
                WHERE u.uid=:uid and w.wochenbeginn=:wochenbeginn
                ) a;';

        $stmt = $this->dbh->prepare($sql);
        /** @var PDOStatement $stmt */
        $stmt->bindParam(':uid', $this->uid);
        $stmt->bindValue(':wochenbeginn', date_format($this->wochenbeginn, 'Y-m-d'));
        $stmt->execute();
        if ($zeile = $stmt->fetch()) {
            if (!is_null($zeile['max_ts']))
                echo('letzte Änderung: ' . strftime('%a, %d.%m.%y %H:%M:%S', strtotime($zeile['max_ts'])));
        }

        $stmt->closeCursor();
    }

    public function echoStimmtSoButton()
    {
        if ( $this->vorgesetzter ) {
            echo('<button type="submit" name="ok" id="ok" value="ok">stimmt -> Buchhaltung</button>');
            echo('<button type="submit" name="ok_next_vorgesetzter" id="ok_next_vorgesetzter" value="ok_next_vorgesetzter">stimmt -> nächster Vorgesetzer</button>');
        }
        else
            echo('<button type="submit" name="ok" id="ok" value="ok">stimmt so</button>');
    }

    public function echoCurrentWeek()
    {
        echo(date_format($this->wochenbeginn, 'W / Y'));
    }

    public function echoMessage()
    {
        echo('<b>'.$this->message.'</b>');
    }

    public function echoCurrentUser()
    {
        echo($this->uid);
    }

    public function echoCreateEintrag()
    {
        $params['week'] = date_format($this->wochenbeginn, 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.editor.create', $params);

        echo('<p><a href="' . $absoluteUrl . '">Zeile hinzufügen</a>');

        $params['copy'] = 'last';
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.editor.create', $params);
        echo(' | <a href="' . $absoluteUrl . '">letzte Zeile kopieren</a></p>');
    }

    public function getLinksToPrevNextWeek()
    {
        $d = clone $this->wochenbeginn;
        $i = new DateInterval('P7D');
        $i->invert = 1;
        $params['week'] = date_format(date_add($d, $i), 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.page.index', $params);

        echo('<a href="' . $absoluteUrl . '">vorherige</a> ');

        $d = clone $this->wochenbeginn;
        $i = new DateInterval('P7D');
        $params['week'] = date_format(date_add($d, $i), 'Y-m-d');
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.page.index', $params);
        echo('| <a href="' . $absoluteUrl . '">nächste</a> ');
    }

    public function selectMitarbeiter()
    {
        if (!$this->isPruefungsBerechtigt())
            return;

        echo('<select name="mitarbeiter">');
        echo('<option value="">&lt;bitte auswählen&gt;</option>');

        $sql = 'SELECT uid FROM oc_zeiterf_user ORDER BY 1;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();

        while ($content = $stmt->fetch()) {
            $s = htmlspecialchars($content['uid']);
            echo('<option value="' . $s . '">' . $s . '</option>');
        }
        $stmt->closeCursor();

        echo('</select>');
    }


    public function echoWeitergabeAn()
    {
        $userList = [];
        $sql = 'SELECT uid FROM oc_zeiterf_user 
            WHERE vorgesetzter or buchhalter ORDER BY 1;';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        while ($content = $stmt->fetch())
            $userList[] = $content['uid'];
        $stmt->closeCursor();

        $userList[] = $this->uid;
        $userList = array_unique($userList);

        echo('<select name="zustaendiger" id="zustaendiger">');
        echo('<option value="">&lt;bitte auswählen&gt;</option>');
        foreach ($userList as $usr) {
            $s = htmlspecialchars($usr);
            echo('<option value="' . $s . '">' . $s . '</option>');
        }
        echo('</select>');
    }


    public function showWochenblatt()
    {
        $sql = "SELECT e.id, 
            to_char(e.datum, 'YYYY-MM-DD') as datum, 
            to_char(e.von, 'HH24:MI') as von, 
            to_char(e.bis, 'HH24:MI') as bis,
            e.feiertag, e.arbeitszeitverlagerung, e.auftragsnr, e.bauvorhaben, 
            e.stunden, e.ueberstunden, e. lohnart,
            e.erschwer_stunden , e.erschwer_nr, e.erschwer_taetigkeit, 
            e.rufbereitschaft, e.verpflegungsmehraufwand,
            w.id as wochenblatt_id
        FROM oc_zeiterf_entry e 
            inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
            inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
        WHERE u.uid=:uid and w.wochenbeginn=:wochenbeginn
        ORDER BY e.datum, e.von, e.bis;";
        $stmt = $this->dbh->prepare($sql);
        /** @var PDOStatement $stmt */
        $stmt->bindParam(':uid', $this->uid);
        $stmt->bindValue(':wochenbeginn', date_format($this->wochenbeginn, 'Y-m-d'));
        $stmt->execute();
        while ($zeile = $stmt->fetch()) {
            $this->echoZeile($zeile);
            $this->wochenblatt_id = $zeile['wochenblatt_id'];
        }

        $stmt->closeCursor();
    }

    private function echoZeile($zeile)
    {
        echo('<tr>');
        echo('<td>' . ($zeile['feiertag'] ? 'F' : '') . '</td>');
        echo('<td>' . ($zeile['arbeitszeitverlagerung'] ? 'A' : '') . '</td>');

        $params['id'] = $zeile['id'];
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.editor.edit', $params);
        $d = strtotime($zeile['datum']);
        echo('<td><a href="' . $absoluteUrl . '">' . strftime('%a, %d.%m.%y', $d) . '</a></td>');

        echo('<td>' . $zeile['von'] . '</td>');
        echo('<td>' . $zeile['bis'] . '</td>');
        echo('<td>' . htmlspecialchars($zeile['auftragsnr']) . '</td>');
        echo('<td>' . htmlspecialchars($zeile['bauvorhaben']) . '</td>');
        $s = number_format($zeile['stunden'], 2, ',', '.');
        if ($zeile['ueberstunden'])
            echo('<td></td><td>' . $s . '</td>');
        else
            echo('<td>' . $s . '</td><td></td>');
        echo('<td>' . $zeile['lohnart'] . '</td>');

        $s = number_format($zeile['erschwer_stunden'], 2, ',', '.');
        echo('<td>' . $s . '</td>');
        echo('<td>' . $zeile['erschwer_nr'] . '</td>');
        echo('<td>' . htmlspecialchars($zeile['erschwer_taetigkeit']) . '</td>');
        echo('<td>' . $zeile['rufbereitschaft'] . '</td>');
        $s = number_format($zeile['verpflegungsmehraufwand'], 2, ',', '.');
        echo('<td>' . $s . '</td>');

        $params['wochenbeginn'] = date_format($this->wochenbeginn, 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.editor.delete', $params);
        echo('<td><a href="' . $absoluteUrl . '"><svg width="20" height="20" viewBox="0 0 20 20" alt="Loeschen">
            <image x="0" y="0" width="20" height="20" preserveAspectRatio="xMinYMin meet" xlink:href="/apps/stundenzettel/img/delete.svg"  class="app-icon"></image></svg></a></td>');

        echo("</tr>\n");
    }

    public function echoWochenExportLink()
    {
        $params['wochenbeginn'] = date_format($this->wochenbeginn, 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.wochenblatt.download', $params);
        echo('<a href="' . $absoluteUrl . '">Excel-Export</a>');
    }

    public function echoWochenblatt_Id()
    {
        echo($this->wochenblatt_id);
    }

    public function echoPruefungFilter()
    {   
       $eigen_checked = '';
       $gruppe_checked = '';
       $alle_checked = '';
       if ( 'alle' == $this->pruef_filter) {
           $alle_checked = ' checked';
       } elseif ( 'gruppe' == $this->pruef_filter) {
           $gruppe_checked = ' checked';
       } else
           $eigen_checked = ' checked';

        $params['week'] = date_format($this->wochenbeginn, 'Y-m-d');
        $params['mitarbeiter'] = $this->uid;
        $absoluteUrl = $this->urlGenerator->linkToRoute('stundenzettel.page.index_post', $params);

        echo('<form action="'.$absoluteUrl.'" name="pruef_filter" method="post">'."\n");

        echo('<input type="radio" id="eigen" name="pruef_filter" value="eigen"'.$eigen_checked.' onClick="this.form.submit();">');
        echo('<label for="eigen">eigene</label>'."\n");
        echo('<input type="radio" id="gruppe" name="pruef_filter" value="gruppe"'.$gruppe_checked.' onClick="this.form.submit();">');
        echo('<label for="gruppe">Vorgesetze/Buchhalter</label>'."\n");
        echo('<input type="radio" id="alle" name="pruef_filter" value="alle"'.$alle_checked.' onClick="this.form.submit();">');
        echo('<label for="alle">Alle</label>'."\n");
        echo('<input type="submit" value="Filter"></form>'."\n");
    }


    public function echoPruefungen()
    {
        $sql = "SELECT to_char(p.eingereicht_am, 'DD.MM.YY HH24:MI') as eingereicht_am, 
            z.uid as zustaendiger, p.level,
            p.kommentar,
            bestanden,
            to_char(p.geprueft_am, 'DD.MM.YY HH24:MI') as geprueft_am,
             g.uid as geprueft_von
            from oc_zeiterf_pruefung p 
                left join oc_zeiterf_user z on (p.zustaendiger=z.id) 
                left join oc_zeiterf_user g on (p.geprueft_von=g.id) 
            WHERE p.oc_zeiterf_wochenblatt_id=:w_id
            ORDER BY p.eingereicht_am, z.uid;";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':w_id', $this->wochenblatt_id);
        $stmt->execute();
        while ($zeile = $stmt->fetch()) {
            $lev = $zeile['level'];
            $s_Level = '';
            if (1 == $lev) $s_Level = ' / Vorgesetzter';
            if (2 == $lev) $s_Level = ' / Buchhaltung';

            echo('<tr>');
            echo('<td>' . $zeile['eingereicht_am'] . '</td>');
            echo('<td>' . htmlspecialchars($zeile['zustaendiger'] . $s_Level) . '</td>');
            echo('<td>' . htmlspecialchars($zeile['kommentar']) . '</td>');
            echo('<td>' . ($zeile['bestanden'] ? 'Ja' : 'Nein') . '</td>');
            echo('<td>' . $zeile['geprueft_am'] . '</td>');
            echo('<td>' . htmlspecialchars($zeile['geprueft_von']) . '</td>');
            echo('</tr>');
        }

        $stmt->closeCursor();
    }

    public function isPruefungsBerechtigt()
    {
        return $this->vorgesetzter || $this->buchhalter;
    }

    public function echoOffenenPruefungen()
    {
       $set_uid = false; 
       if ( 'alle' == $this->pruef_filter) {
           $where = '';
       } elseif ( 'gruppe' == $this->pruef_filter) {
           if ($this->vorgesetzter)
               $where = " and p.level=1";
           elseif ($this->buchhalter)
               $where = " and p.level=2";
           else  # wenn man weder Buchhalter noch Vorgesetzter ist wird nichts angezeigt
               $where = ' and false';
       } else {
           $where = " and z.uid=:uid";
           $set_uid = true; 
       }

        $sql = "SELECT to_char(p.eingereicht_am, 'DD.MM.YY HH24:MI') as eingereicht_am, 
            m.uid as mitarbeiter, 
            z.uid as zustaendiger, p.level,
            p.kommentar,
            to_char(w.wochenbeginn, 'YY-MM-DD') as wochenbeginn, 
            to_char(w.wochenbeginn, 'IW / YYYY') as kw
            from oc_zeiterf_pruefung p 
                inner join oc_zeiterf_wochenblatt w on (p.oc_zeiterf_wochenblatt_id=w.id)
                left join oc_zeiterf_user m on (w.oc_zeiterf_user_id=m.id) 
                left join oc_zeiterf_user z on (p.zustaendiger=z.id) 
            WHERE geprueft_am is null ". $where ."
            ORDER BY w.wochenbeginn, m.uid ;";
        $stmt = $this->dbh->prepare($sql);
        $set_uid && $stmt->bindParam(':uid', $this->uid);
        $stmt->execute();
        while ($zeile = $stmt->fetch()) {
            $lev = $zeile['level'];
            $s_Level = '';
            if (1 == $lev) $s_Level = ' / Vorgesetzter';
            if (2 == $lev) $s_Level = ' / Buchhaltung';

            $params['week'] = $zeile['wochenbeginn'];
            $params['mitarbeiter'] = $zeile['mitarbeiter'];
            $w_url = $this->urlGenerator->linkToRoute('stundenzettel.page.index', $params);

            echo('<tr>');
            echo('<td><a href="' . $w_url . '">' . $zeile['eingereicht_am'] . '</a></td>');
            echo('<td>' . $zeile['kw'] . '</td>');
            echo('<td>' . htmlspecialchars($zeile['mitarbeiter']) . '</td>');
            echo('<td>' . htmlspecialchars($zeile['zustaendiger'] . $s_Level) . '</td>');
            echo('<td>' . htmlspecialchars($zeile['kommentar']) . '</td>');
            echo('</tr>');
        }

        $stmt->closeCursor();
    }
}
