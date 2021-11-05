<?php

namespace OCA\Bestand\Controller;

use OCA\Bestand\AppInfo\Application;
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
    public function delete($id): RedirectResponse
    {
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $params = [];
            $params['error_msg'] = 'unbekannter Kollege';
        } else {
            $sql = 'Delete from deleteBestand(:id, :logged_in_user);';
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
            }
            $stmt->closeCursor();
            if (False === $params) {
                $params = [];
                $params['error_msg'] = "nix geloescht.";
            }
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $absoluteUrl = $urlGenerator->linkToRoute('bestand.page.index', $params);
        return new RedirectResponse($absoluteUrl);
    }


    /**
     * @return PDO
     */
    private function getDbh(): \PDO
    {
        include('bestand/lib/config.php');

        $conn = $db_config['system'] . ':host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'] . ';port=' . $db_config['port'];
        $dbh = new \PDO($conn, $db_config['user'], $db_config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    /**
     *
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     */
    public function update(
        $id,
        $kategorie,
        $inventar_nr,
        $serien_nr,
        $weitere_nr,
        $geheim_nr,
        $bezeichnung,
        $typenbezeichnung,
        $lieferant,
        $standort,
        $nutzer,
        $st_beleg_nr,
        $zubehoer,
        $st_inventar_nr,
        $stb_inventar_nr,
        $konto,
        $bemerkung,
        $fluke_nr,
        $anschaffungswert,
        $anschaffungsdatum,
        $prueftermin1,
        $prueftermin2,
        $ausgabedatum,
        $ruecknahmedatum
    ): \OCP\AppFramework\Http\Response
    {

        // TODO checkKategorie

        $dbh = $this->getDbh();
        $params = False;
        $error_msg = '';
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) $error_msg = 'unbekannter Kollege';

        if (0 === strlen($error_msg)) {
            $sql = 'Update oc_bdb_bestand set
                    kategorie = :kategorie,
                    inventar_nr = :inventar_nr,
                    serien_nr = :serien_nr,
                    weitere_nr = :weitere_nr,
                    geheim_nr = :geheim_nr,
                    bezeichnung = :bezeichnung,
                    typenbezeichnung = :typenbezeichnung,
                    lieferant = :lieferant,
                    standort = :standort,
                    nutzer = :nutzer,
                    st_beleg_nr = :st_beleg_nr,
                    zubehoer = :zubehoer,
                    st_inventar_nr = :st_inventar_nr,
                    stb_inventar_nr = :stb_inventar_nr,
                    konto = :konto,
                    bemerkung = :bemerkung,
                    fluke_nr = :fluke_nr,
                    anschaffungswert = :anschaffungswert,
                    anschaffungsdatum = :anschaffungsdatum,
                    prueftermin1 = :prueftermin1,
                    prueftermin2 = :prueftermin2,
                    ausgabedatum = :ausgabedatum,
                    ruecknahmedatum = :ruecknahmedatum
                where id = :id;';

            $id = is_numeric($id) ? intval($id) : 0;
            $anschaffungswert = is_numeric($anschaffungswert) ? intval($anschaffungswert) : 0;


            $stmt = $dbh->prepare($sql);
            $sql_params = [':id' => $id,
                ':kategorie' => $kategorie,
                ':inventar_nr' => $inventar_nr,
                ':serien_nr' => $serien_nr,
                ':weitere_nr' => $weitere_nr,
                ':geheim_nr' => $geheim_nr,
                ':bezeichnung' => $bezeichnung,
                ':typenbezeichnung' => $typenbezeichnung,
                ':lieferant' => $lieferant,
                ':standort' => $standort,
                ':nutzer' => $nutzer,
                ':st_beleg_nr' => $st_beleg_nr,
                ':zubehoer' => $zubehoer,
                ':st_inventar_nr' => $st_inventar_nr,
                ':stb_inventar_nr' => $stb_inventar_nr,
                ':konto' => $konto,
                ':bemerkung' => $bemerkung,
                ':fluke_nr' => $fluke_nr,
                ':anschaffungswert' => $anschaffungswert,
                ':anschaffungsdatum' => $anschaffungsdatum,
                ':prueftermin1' => $prueftermin1,
                ':prueftermin2' => $prueftermin2,
                ':ausgabedatum' => $ausgabedatum,
                ':ruecknahmedatum' => $ruecknahmedatum
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
            $params['kategorie'] = $kategorie;
            $params['inventar_nr'] = $inventar_nr;
            $params['serien_nr'] = $serien_nr;
            $params['weitere_nr'] = $weitere_nr;
            $params['geheim_nr'] = $geheim_nr;
            $params['bezeichnung'] = $bezeichnung;
            $params['typenbezeichnung'] = $typenbezeichnung;
            $params['lieferant'] = $lieferant;
            $params['standort'] = $standort;
            $params['nutzer'] = $nutzer;
            $params['st_beleg_nr'] = $st_beleg_nr;
            $params['zubehoer'] = $zubehoer;
            $params['st_inventar_nr'] = $st_inventar_nr;
            $params['stb_inventar_nr'] = $stb_inventar_nr;
            $params['konto'] = $konto;
            $params['bemerkung'] = $bemerkung;
            $params['fluke_nr'] = $fluke_nr;
            $params['anschaffungswert'] = $anschaffungswert;
            $params['anschaffungsdatum'] = $anschaffungsdatum;
            $params['prueftermin1'] = $prueftermin1;
            $params['prueftermin2'] = $prueftermin2;
            $params['ausgabedatum'] = $ausgabedatum;
            $params['ruecknahmedatum'] = $ruecknahmedatum;

            $params['error_msg'] = $error_msg;

            return new RedirectResponse($urlGenerator->linkToRoute('bestand.editor.create', $params));
        }

        if (0 >= strlen($submit_next)) { # back to Wochenuebersicht
            $absoluteUrl = $urlGenerator->linkToRoute('bestand.page.index', $params);
        } else {
            $absoluteUrl = $this->getUrlForNextEntry($dbh, $id, $urlGenerator, $uid, $datum);
        }
        return new RedirectResponse($absoluteUrl);
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
        Util::addStyle(Application::APP_ID, 'bestand');

        $params['id'] = $id;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }

    /**
     * @return TemplateResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function create($error_msg): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'bestand');

        $params['error_msg'] = $error_msg;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }
}
