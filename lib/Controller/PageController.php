<?php

namespace OCA\Stundenzettel\Controller;

use OCA\Stundenzettel\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\Util;
use PDO;

class PageController extends Controller
{

    private $unterzeichner;

    /**
     * PageController constructor.
     * @param IRequest $request
     */
    public function __construct(IRequest $request)
    {
        parent::__construct(Application::APP_ID, $request);
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
    public function index($week, $mitarbeiter, $message, $pruef_filter): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'stundenzettel');

        $params['week'] = $week;
        $params['mitarbeiter'] = $mitarbeiter;
        $params['message'] = $message;
        $params['pruef_filter'] = $pruef_filter;
        return new TemplateResponse(Application::APP_ID, 'main', $params);
    }

    /**
     * @param string week
     * @param string mitarbeiter
     *
     * @return \OCP\AppFramework\Http\Response
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function indexPost($week, $mitarbeiter, $pruef_filter): \OCP\AppFramework\Http\Response
    {
        if (0 < strlen($week) || (0 < strlen($mitarbeiter))) {
            $params['week'] = $week;
            $params['mitarbeiter'] = $mitarbeiter;
            $params['pruef_filter'] = $pruef_filter;
            $urlGenerator = \OC::$server->getURLGenerator();
            $absoluteUrl = $urlGenerator->linkToRoute('stundenzettel.page.index', $params);
            return new RedirectResponse($absoluteUrl);
        }

        Util::addStyle(Application::APP_ID, 'stundenzettel');
        return new TemplateResponse(Application::APP_ID, 'main');
    }

    /**
     * @param integer wochenblatt_id
     * @param string zustaendiger
     * @param string edit_comment
     * @param string ok - der "stimmt so" Button wurde gedrueckt
     * @param string not_ok - der "zurueck" Button wurde gedrueckt
     *
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function pruefung($wochenblatt_id, $zustaendiger, $edit_comment, $ok, $ok_next_vorgesetzter, $not_ok): RedirectResponse
    {
        $bestanden = 0;
        $next_vorgesetzter = 0;
        if ('not_ok' !== $not_ok) {
            if ('ok_next_vorgesetzter' === $ok_next_vorgesetzter) {
                $next_vorgesetzter = 1;
                $bestanden = 1;
            } else
                $bestanden = ('ok' === $ok) ? 1 : 0;
        }

        $dbh = $this->getDbh();
        $params = $this->getRedirectParams($dbh, $wochenblatt_id);
        try {
            $this->insertPruefung($dbh, $wochenblatt_id, $zustaendiger, $edit_comment, $bestanden, $next_vorgesetzter);
            if ( 1 === $bestanden && ( 1 !== $next_vorgesetzter)) $this->saveForBuchhalter($dbh, $wochenblatt_id);
        } catch (\Exception $e) {
            $m = $e->getMessage();
            if (str_starts_with($m, 'SQLSTATE[P0001]: Raise exception: 7 ERROR:  insertPruefung -')) {
                $m = substr($m, 60);
                $pos = strpos($m, 'CONTEXT:  PL/pgSQL function insertpruefung');
                if (0 < $pos)
                    $m = substr($m, 0, $pos);
            }

            $params['message'] = $m;
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $absoluteUrl = $urlGenerator->linkToRoute('stundenzettel.page.index', $params);
        return new RedirectResponse($absoluteUrl);
    }

    private function saveForBuchhalter($dbh, $wochenblatt_id) {
        $sql = 'Select buchhalter from oc_zeiterf_user where uid=:unterzeichner;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':unterzeichner', $this->unterzeichner);
        $stmt->execute();
        $content = $stmt->fetch();
        $stmt->closeCursor();
        if ((false === $content) || (false === $content['buchhalter'])) return;

        $sql = 'Select u.uid, w.wochenbeginn from oc_zeiterf_wochenblatt w 
            inner join oc_zeiterf_user u on (u.id=w.oc_zeiterf_user_id)  
            where w.id=:wochenblatt_id;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':wochenblatt_id', $wochenblatt_id);
        $stmt->execute();
        $content = $stmt->fetch();
        $stmt->closeCursor();

        $w = new WochenblattController($this->request); 
        $xlsx_temp_file = $w->createWochenblatt($content['wochenbeginn'], $content['uid']);

        try {
            $w_beginn = strtotime($content['wochenbeginn']);
        } catch (\Exception $e) {
            return null;  // IDEA Message rausbringen
        }
        $jahr = strftime('%Y', $w_beginn);

        $userFolder = \OC::$server->getUserFolder('buchhaltung');
        if (!$userFolder->nodeExists($jahr)) {
            $userFolder->newFolder($jahr);
        }

        $fileName = $jahr . '/' . $w->getDownloadFilename();

        try {
            $file = $userFolder->get($fileName);
        } catch (NotFoundException $e) {
            $file = $userFolder->newFile($fileName);
        }
        $file->putContent(file_get_contents($xlsx_temp_file));
        unlink($xlsx_temp_file);
    }

    /**
     * @param PDO $dbh
     * @param int $wochenblatt_id
     * @param String $zustaendiger
     * @param String $edit_comment
     * @param int $bestanden
     * @param int $next_vorgesetzter
     *
     * @throws \Exception
     */
    private function insertPruefung($dbh, $wochenblatt_id, $zustaendiger, $edit_comment, $bestanden, $next_vorgesetzter) {
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) throw new \Exception('user missing');

        $this->unterzeichner = $user->getUID();

        $sql = 'Select insertPruefung(:wochenblatt_id, :kommentar, :zustaendiger, :unterzeichner, :bestanden, :next_vorgesetzter);';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':wochenblatt_id', $wochenblatt_id);
        $stmt->bindParam(':kommentar', $edit_comment);
        $stmt->bindParam(':zustaendiger', $zustaendiger);
        $stmt->bindParam(':unterzeichner', $this->unterzeichner);
        $stmt->bindParam(':bestanden', $bestanden);
        $stmt->bindParam(':next_vorgesetzter', $next_vorgesetzter);
        $stmt->execute();
        $stmt->closeCursor();
    }

    /**
     * @param PDO $dbh
     * @param int $wochenblatt_id
     *
     * @return array
     */
    private function getRedirectParams($dbh, $wochenblatt_id)
    {
        $params = [];
        $sql = 'select w.wochenbeginn, u.uid
            FROM oc_zeiterf_wochenblatt w 
                inner join oc_zeiterf_user u on (w.oc_zeiterf_user_id=u.id)
            WHERE w.id=:wochenblatt_id;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':wochenblatt_id', $wochenblatt_id);
        $stmt->execute();
        if ($content = $stmt->fetch()) {
            $params['week'] = $content['wochenbeginn'];
            $params['mitarbeiter'] = $content['uid'];
        }
        $stmt->closeCursor();

        return $params;
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
}
