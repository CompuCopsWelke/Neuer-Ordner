<?php

namespace OCA\Stundenzettel\Controller;

use OCA\Stundenzettel\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\Util;
use PDO;

class EditorController extends Controller
{

    /**
     * EditorController constructor.
     * @param IRequest $request
     */
    public function __construct(IRequest $request)
    {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * @param string id
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function delete($id, $wochenbeginn, $mitarbeiter): RedirectResponse
    {
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $params = [];
            $params['error_msg'] = 'unbekannter Kollege';
        } else {
            $sql = 'Select * from deleteZeitErfEntry(:id, :logged_in_user);';
            $stmt = $this->getDbh()->prepare($sql);
            $params = False;
            $sql_params = [
                ':id' => $id,
                ':logged_in_user' => $user->getUID()
                 ];
            try {
                $stmt->execute($sql_params);
                $params = $stmt->fetch();
            } catch (\Exception $e) {
                $params = [];
                $params['message'] = $e->getMessage();
                $params['week'] = $wochenbeginn;
                $params['mitarbeiter'] = $mitarbeiter;
            }
            $stmt->closeCursor();
            if (False === $params) {
                $params = [];
                $params['error_msg'] = "nix geloescht.";
            }
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $absoluteUrl = $urlGenerator->linkToRoute('stundenzettel.page.index', $params);
        return new RedirectResponse($absoluteUrl);
    }


    /**
     * @return PDO
     */
    private function getDbh(): \PDO
    {
        include('stundenzettel/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $dbh = new \PDO($conn, $db_config['user'], $db_config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    /**
     * @param int $id
     * @param int $lohnart
     * @param bool $arbeitszeitverlagerung
     * @param String $auftragsnr
     * @param String $bauvorhaben
     * @param String $bis
     * @param String $datum
     * @param String $erschwer_nr
     * @param float $erschwer_stunden
     * @param String $erschwer_taetigkeit
     * @param bool $feiertag
     * @param bool $rufbereitschaft
     * @param float $stunden
     * @param bool $ueberstunden
     * @param float $verpflegungsmehraufwand
     * @param String $von
     * @param String $uid
     *
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     */
    public function update($id,
                           $lohnart,
                           $arbeitszeitverlagerung,
                           $auftragsnr,
                           $bauvorhaben,
                           $bis,
                           $datum,
                           $erschwer_nr,
                           $erschwer_stunden,
                           $erschwer_taetigkeit,
                           $feiertag,
                           $rufbereitschaft,
                           $stunden,
                           $ueberstunden,
                           $verpflegungsmehraufwand,
                           $von,
                           $uid,
                           $submit_next
                       ): \OCP\AppFramework\Http\Response
    {

        $b_feiertag = ($feiertag == 'feiertag') ? 1 : 0;
        $b_arbeitszeitverlagerung = ($arbeitszeitverlagerung == 'arbeitszeitverlagerung') ? 1 : 0;
        $b_ueberstunden = ($ueberstunden == 'ueberstunden' ? 1 : 0);
        $b_rufbereitschaft = ($rufbereitschaft == 'rufbereitschaft' ? 1 : 0);

        $dbh = $this->getDbh();
        $params = False;
        $error_msg = '';
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) $error_msg = 'unbekannter Kollege';

        if (0 === strlen($error_msg)) {
            $sql = 'Select * from updateZeitErfEntry(:id, :lohnart,
                :arbeitszeitverlagerung, :auftragsnr, :bauvorhaben, :bis, :datum,
                :erschwer_nr, :erschwer_stunden, :erschwer_taetigkeit, :feiertag,
                :rufbereitschaft, :stunden, :ueberstunden,
                :verpflegungsmehraufwand, :von, :mitarbeiter, :logged_in_user);';

            $id = is_numeric($id) ? intval($id) : 0;
            $lohnart = is_numeric($lohnart) ? intval($lohnart) : 100;
            $stunden = is_numeric($stunden) ? floatval($stunden) : 0.0;
            $erschwer_stunden = is_numeric($erschwer_stunden) ? floatval($erschwer_stunden) : null;
            $verpflegungsmehraufwand = is_numeric($verpflegungsmehraufwand) ? floatval($verpflegungsmehraufwand) : null;

            $sql_bis = '00:00' == $bis ? '24:00' : $bis;

            $stmt = $dbh->prepare($sql);
            $sql_params = [':id' => $id,
                ':lohnart' => $lohnart,
                ':arbeitszeitverlagerung' => $b_arbeitszeitverlagerung,
                ':auftragsnr' => $auftragsnr,
                ':bauvorhaben' => $bauvorhaben,
                ':bis' => $sql_bis,
                ':datum' => $datum,
                ':erschwer_nr' => $erschwer_nr,
                ':erschwer_stunden' => $erschwer_stunden,
                ':erschwer_taetigkeit' => $erschwer_taetigkeit,
                ':feiertag' => $b_feiertag,
                ':rufbereitschaft' => $b_rufbereitschaft,
                ':stunden' => $stunden,
                ':ueberstunden' => $b_ueberstunden,
                ':verpflegungsmehraufwand' => $verpflegungsmehraufwand,
                ':von' => $von,
                ':mitarbeiter' => $uid,
                ':logged_in_user' => $user->getUID()
                ];
            try {
                $stmt->execute($sql_params);
                $params = $stmt->fetch();
            } catch (\Exception $e) {
                $error_msg = $e->getMessage();
            }
            $stmt->closeCursor();
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        if (False === $params) {
            $params = [];
            $params['feiertag'] = $b_feiertag;
            $params['arbeitszeitverlagerung'] = $b_arbeitszeitverlagerung;
            $params['datum'] = $datum;
            $params['von'] = $von;
            $params['bis'] = $bis;
            $params['auftragsnr'] = $auftragsnr;
            $params['bauvorhaben'] = $bauvorhaben;
            $params['lohnart'] = $lohnart;
            $params['erschwer_nr'] = $erschwer_nr;
            $params['erschwer_stunden'] = $erschwer_stunden;
            $params['erschwer_taetigkeit'] = $erschwer_taetigkeit;
            $params['stunden'] = $stunden;
            $params['ueberstunden'] = $b_ueberstunden;
            $params['rufbereitschaft'] = $b_rufbereitschaft;
            $params['verpflegungsmehraufwand'] = $verpflegungsmehraufwand;
            $params['error_msg'] = $error_msg;

            $params['week'] = $datum;
            $params['mitarbeiter'] = $uid;
            return new RedirectResponse($urlGenerator->linkToRoute('stundenzettel.editor.create', $params));
        }

        if (0 >= strlen($submit_next)) { # back to Wochenuebersicht
            $absoluteUrl = $urlGenerator->linkToRoute('stundenzettel.page.index', $params);
        } else {
            $absoluteUrl = $this->getUrlForNextEntry($dbh, $id, $urlGenerator, $uid, $datum);
        }
        return new RedirectResponse($absoluteUrl);
    }

    /**
     * getUrlForNextEntry ermittelt die Folge-URL beim Klick auf "Speichern und Nächster".
     *
     * @param \PDO $dbh
     * @param integer $prev_id ,
     * @param  $uid
     * @param  $datum
     * @return String die ermittelte URL
     */
    private function getUrlForNextEntry($dbh, $prev_id, $urlGenerator, $uid, $datum) {
        $params = [];
        if (0 < $prev_id) {
            $next_id = $this->getNextId($dbh, $prev_id);
            if (false !== $next_id) {
                $params['id'] = $next_id;
                return $urlGenerator->linkToRoute('stundenzettel.editor.edit', $params);
            }
        }

        $params['week'] = $datum;
        $params['mitarbeiter'] = $uid;
        return $urlGenerator->linkToRoute('stundenzettel.editor.create', $params);
    }

    /**
     * getNextId ermittelt zu einer gegebenen EintragsID den nächsten Id innerhalb der selben Woche
     *
     * @param \PDO $dbh
     * @param integer $prev_id ,
     * @return false|integer
     */
    private function getNextId($dbh, $prev_id) {
        $params = false;
        $sql = 'Select e.id from oc_zeiterf_entry e inner join 
                (select oc_zeiterf_wochenblatt_id, datum, von, id from oc_zeiterf_entry Where id=:id) w
                on (e.oc_zeiterf_wochenblatt_id = w.oc_zeiterf_wochenblatt_id)
                where ((e.datum > w.datum) or (e.datum=w.datum and e.von >= w.von)) AND e.id <> w.id
                Order by e.datum, e.von, e.id
                Limit 1;';
        $stmt = $dbh->prepare($sql);
        $sql_params = [':id' => $prev_id];
        try {
            $stmt->execute($sql_params);
            $params = $stmt->fetch();
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }
        $stmt->closeCursor();
        return false === $params ? false : $params['id'];
    }

    /**
     * @param int id
     *
     * @return TemplateResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function edit($id): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'stundenzettel');

        $params['id'] = $id;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }

    /**
     * @param string week
     * @param string mitarbeiter
     *
     * @return TemplateResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function create($week, $mitarbeiter, $copy, $error_msg,
                           $lohnart,
                           $arbeitszeitverlagerung,
                           $auftragsnr,
                           $bauvorhaben,
                           $bis,
                           $datum,
                           $erschwer_nr,
                           $erschwer_stunden,
                           $erschwer_taetigkeit,
                           $feiertag,
                           $rufbereitschaft,
                           $stunden,
                           $ueberstunden,
                           $verpflegungsmehraufwand,
                           $von
    ): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'stundenzettel');

        if ('last' == $copy ) {
            $params = $this->copyLast($week, $mitarbeiter);
            if (False === $params) {
                # leere Editor praesentieren
                $params = [];
                $params['week'] = $week;
                $params['mitarbeiter'] = $mitarbeiter;
            }
        } else {
            $params['feiertag'] = $feiertag;
            $params['arbeitszeitverlagerung'] = $arbeitszeitverlagerung;
            0 < strlen($datum) && $params['datum'] = $datum;

            if (8 == strlen($von)) 
                $von = substr($von, 0, 5);

            if (8 == strlen($bis)) 
                $bis = substr($bis, 0, 5);
            
            if ('24:00' == $bis) 
                $bis = '00:00';

            $params['von'] = $von;
            $params['bis'] = $bis;
            $params['auftragsnr'] = $auftragsnr;
            $params['bauvorhaben'] = $bauvorhaben;
            $params['lohnart'] = $lohnart;
            $params['erschwer_nr'] = $erschwer_nr;
            $params['erschwer_stunden'] = $erschwer_stunden;
            $params['erschwer_taetigkeit'] = $erschwer_taetigkeit;
            $params['stunden'] = $stunden;
            $params['ueberstunden'] = $ueberstunden;
            $params['rufbereitschaft'] = $rufbereitschaft;
            $params['verpflegungsmehraufwand'] = $verpflegungsmehraufwand;

            $params['week'] = $week;
            $params['mitarbeiter'] = $mitarbeiter;
        }
        $params['error_msg'] = $error_msg;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }


    /**
     * @param string week
     * @param string mitarbeiter
     *
     * @return params für den neunen Editor
     **/
    private function copyLast($week, $mitarbeiter) 
    {
        $sql = "Select e.datum + integer '1' as datum, 
                feiertag, arbeitszeitverlagerung,
                to_char(e.von, 'HH24:MI') as von, to_char(e.bis, 'HH24:MI') as bis,
                auftragsnr, bauvorhaben, lohnart, erschwer_nr, erschwer_stunden,
                erschwer_taetigkeit, stunden, ueberstunden, rufbereitschaft,
                verpflegungsmehraufwand
                FROM oc_zeiterf_entry e 
                    inner join oc_zeiterf_wochenblatt w on (e.oc_zeiterf_wochenblatt_id=w.id)
                    inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
                WHERE u.uid=:uid and w.wochenbeginn=:wochenbeginn
                Order by e.datum desc, e.von desc, e.id
                Limit 1;";
        $stmt = $this->getDbh()->prepare($sql);
        $stmt->bindParam(':uid', $mitarbeiter);
        $stmt->bindParam(':wochenbeginn', $week);
        try {
            $stmt->execute();
            $params = $stmt->fetch();
            if (False !== $params) {
                if ('24:00' == $params['bis']) 
                    $params['bis'] = '00:00';

                # id entfernen
                $params['id'] = ''; 
            }
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }
        $stmt->closeCursor();
        return $params;
    }
}
