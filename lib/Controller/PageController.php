<?php

namespace OCA\Bestand\Controller;

use OCA\Bestand\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\Util;
use PDO;

class PageController extends Controller
{
    /**
     * PageController constructor.
     * @param IRequest $request
     */
    public function __construct(IRequest $request)
    {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * @return TemplateResponse
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     **/
    public function index($kategorie, $suchfeld, $suchtext, $datumfeld, $von, $bis, $sort, $message): TemplateResponse
    {
        $params = [];
        if (0 < strlen($kategorie)) $params['kategorie'] = $kategorie;
        if (0 < strlen($suchfeld)) $params['suchfeld'] = $suchfeld;
        if (0 < strlen($suchtext)) $params['suchtext'] = $suchtext;
        if (0 < strlen($datumfeld)) $params['datumfeld'] = $datumfeld;
        if (0 < strlen($von)) $params['von'] = $von;
        if (0 < strlen($bis)) $params['bis'] = $bis;
        if (0 < strlen($sort)) $params['sort'] = $sort;
        if (0 < strlen($message)) $params['message'] = $message;

        Util::addStyle(Application::APP_ID, 'bestand');
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
    public function indexPost($kategorie, $suchfeld, $suchtext, $datumfeld, $von, $bis, $sort): \OCP\AppFramework\Http\Response
    {
        $params = [];
        if (0 < strlen($kategorie)) $params['kategorie'] = $kategorie;
        if (0 < strlen($suchfeld)) $params['suchfeld'] = $suchfeld;
        if (0 < strlen($suchtext)) $params['suchtext'] = $suchtext;
        if (0 < strlen($datumfeld)) $params['datumfeld'] = $datumfeld;
        if (0 < strlen($von)) $params['von'] = $von;
        if (0 < strlen($bis)) $params['bis'] = $bis;
        if (0 < strlen($sort)) $params['sort'] = $sort;

        if (0 < count($params)) {
            $urlGenerator = \OC::$server->getURLGenerator();
            $absoluteUrl = $urlGenerator->linkToRoute('bestand.page.index', $params);
            return new RedirectResponse($absoluteUrl);
        }

        Util::addStyle(Application::APP_ID, 'bestand');
        return new TemplateResponse(Application::APP_ID, 'main');
    }
}
