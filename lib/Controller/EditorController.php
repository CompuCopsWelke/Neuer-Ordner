<?php

namespace OCA\Bestand\Controller;

use OCA\Bestand\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
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
    public function delete($id, $letzte_kategorie): RedirectResponse
    {
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) {
            $params = [];
            $params['error_msg'] = 'unbekannter Kollege';
        } else {
            $sql = 'Select deleteBestand(:id, :logged_in_user);';
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
                $params['error_msg'] = 'nix geloescht.';
            }
        }

        $urlGenerator = \OC::$server->getURLGenerator();

        if (0 < strlen($letzte_kategorie))
            $params['kategorie'] = $letzte_kategorie;
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
        $ruecknahmedatum,
        $letzte_kategorie
    ): \OCP\AppFramework\Http\RedirectResponse
    {
        $dbh = $this->getDbh();
        $params = False;
        $error_msg = '';
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) $error_msg = 'unbekannter Kollege';

        if (0 === strlen($error_msg)) {
            $sql = 'Select * from updateBestand(:id,
                    :kategorie, :inventar_nr, :serien_nr, :weitere_nr, :geheim_nr,
                    :bezeichnung, :typenbezeichnung, :lieferant, :standort, :nutzer, :st_beleg_nr,
                    :zubehoer, :st_inventar_nr, :stb_inventar_nr, :konto, :bemerkung, :fluke_nr,
                    :anschaffungswert, :anschaffungsdatum, :prueftermin1, :prueftermin2,
                    :ausgabedatum, :ruecknahmedatum, :logged_in_user
                    );';

            $id = is_numeric($id) ? intval($id) : 0;
            if (0 >= $id) $id = null;

            $anschaffungswert = is_numeric($anschaffungswert) ? floatval($anschaffungswert) : 0;
            if (0 >= strlen($anschaffungsdatum)) $anschaffungsdatum = null;
            if (0 >= strlen($ausgabedatum)) $ausgabedatum = null;
            if (0 >= strlen($ruecknahmedatum)) $ruecknahmedatum = null;
            if (0 >= strlen($prueftermin1)) $prueftermin1 = null;
            if (0 >= strlen($prueftermin2)) $prueftermin2 = null;

            $stmt = $dbh->prepare($sql);
            $sql_params = [
                ':id' => $id,
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
                ':ruecknahmedatum' => $ruecknahmedatum,

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

        if (0 < strlen($error_msg))
            $params['message'] = $error_msg;

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
            $params['letzte_kategorie'] = $letzte_kategorie;

            return new RedirectResponse($urlGenerator->linkToRoute('bestand.editor.create', $params));
        }

        $params['kategorie'] = $letzte_kategorie;
        $absoluteUrl = $urlGenerator->linkToRoute('bestand.page.index', $params);
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
    public function edit($id, $message, $letzte_kategorie): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'bestand');

        $params['id'] = $id;
        $params['message'] = $message;
        $params['letzte_kategorie'] = $letzte_kategorie;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }

    /**
     * @return TemplateResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function create($error_msg,
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
                           $ruecknahmedatum,
                           $letzte_kategorie
    ): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'bestand');

        $params['error_msg'] = $error_msg;
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
        $params['letzte_kategorie'] = $letzte_kategorie;
        return new TemplateResponse(Application::APP_ID, 'editor', $params);
    }

    /**
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function addDoc($bestand_id, $titel): RedirectResponse
    {
        // if (!is_set($bestand_id) || (0 >= $bestand_id)

        $uploadedFile = $this->request->getUploadedFile('datei_document');
        $ret_params = [];
        $ret_params['id'] = $bestand_id;
        if ((!is_null($uploadedFile)) && (0 === $uploadedFile['error'])) {
            $f = $uploadedFile['tmp_name'];
            $f_content = file_get_contents($f);
            $mime_type = $uploadedFile['type'];
            unlink($f);

            $user = \OC::$server->getUserSession()->getUser();
            if (null === $user)
                $ret_params['message'] = 'unbekannter Kollege';
            else {
                $params = False;
                $sql = 'Select * from addDoc2Bestand(:bestand_id, :titel, :dateiname, :content, :mimetype, :logged_in_user);';
                $dbh = $this->getDbh();
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':bestand_id', $bestand_id);
                $stmt->bindParam(':titel', $titel);
                $stmt->bindParam(':dateiname', $uploadedFile['name']);
                $stmt->bindParam(':content', $f_content, PDO::PARAM_LOB);
                $stmt->bindParam(':mimetype', $mime_type);
                $stmt->bindValue(':logged_in_user', $user->getUID());
                try {
                    $stmt->execute();
                    $ret_params = $stmt->fetch();
                    if (False === $ret_params)
                        $ret_params['message'] = 'unbekannter Kollege: ' . $user->getUID();
                    $ret_params['id'] = $bestand_id;
                } catch (\Exception $e) {
                    $ret_params['message'] = $e->getMessage();
                }
                $stmt->closeCursor();
            }

        } else
            $ret_params['message'] = 'Datei fehlerhaft: ' . $uploadedFile['error'];

        $urlGenerator = \OC::$server->getURLGenerator();
        $edit_url = $urlGenerator->linkToRoute('bestand.editor.edit', $ret_params);
        return new RedirectResponse($edit_url);
    }

    /**
     *
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function showDoc($doc_id)
    {
        $dbh = $this->getDbh();
        $message = '';
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user)
            $message = 'unbekannter Kollege';

        $sql = 'Select dateiname, mt.name as mimetype, content
            from oc_bdb_doc d 
            inner join oc_bdb_mimetype mt on (d.mimetype=mt.id)
            inner join oc_bdb_bestand b on (b.id=d.bestand)
            inner join oc_bdb_kategorie_perm p on (p.kategorie=b.kategorie)
            Where d.id=:doc_id and (p.write or p.read) and p.uid =:uid;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':uid', $user->getUID());

        $stmt->bindParam(':doc_id', $doc_id);
        try {
            $stmt->execute();
            $stmt->bindColumn('content', $lob, PDO::PARAM_STR);
            $zeile = $stmt->fetch();
            if (False !== $zeile) {
                $r = new DataDownloadResponse($lob, $zeile['dateiname'],
                    $zeile['mimetype']);
            } else {
                $message = "Dokument nicht gefunden: $doc_id";
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $stmt->closeCursor();

        if (False === $zeile) {
            $params['message'] = $message;
            $urlGenerator = \OC::$server->getURLGenerator();
            $url = $urlGenerator->linkToRoute('bestand.page.index', $params);
            $r = new RedirectResponse($url);
        }

        return $r;
    }

    /**
     * @return RedirectResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function delDoc($doc_id): RedirectResponse
    {
        $params = [];

        $dbh = $this->getDbh();
        $params = False;
        $message = '';
        $user = \OC::$server->getUserSession()->getUser();
        if (null === $user) $message = 'unbekannter Kollege';

        $sql = 'Select * from delDocFromBestand(:doc_id, :logged_in_user);';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindValue(':logged_in_user', $user->getUID());
        try {
            $stmt->execute();
            $params = $stmt->fetch();
            $params['id'] = $params[0];
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $stmt->closeCursor();

        if (False === $params)
            $params['message'] = $message;

        $urlGenerator = \OC::$server->getURLGenerator();
        $edit_url = $urlGenerator->linkToRoute('bestand.editor.edit', $params);
        return new RedirectResponse($edit_url);
    }
}

/**
 * Class DataDownloadResponse
 * Daten aus dem RAM/Datenbank und Filename gibt es in Nextcloud nicht fertig.
 *
 * @package OCA\Bautagebuch\Controller
 */
class DataDownloadResponse extends Response
{
    /**
     * @var string
     */
    private $data;

    /**
     * Creates a response that prompts the user to download the text
     * @param string $data text to be downloaded
     * @param string $filename the name that the downloaded file should have
     * @param string $contentType the mimetype that the downloaded file should have
     */
    public function __construct($data, $filename, $contentType)
    {
        parent::__construct();

        $this->data = $data;
        $this->addHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->addHeader('Content-Type', $contentType);
        $this->addHeader('Content-Disposition', 'inline; filename=""');
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->data;
    }
}
