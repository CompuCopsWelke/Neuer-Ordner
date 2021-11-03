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
    public function index(): TemplateResponse
    {
        Util::addStyle(Application::APP_ID, 'bestand');

        // TODO $params['week'] = $week;
        return new TemplateResponse(Application::APP_ID, 'main'); # , $params
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
            $absoluteUrl = $urlGenerator->linkToRoute('bestand.page.index', $params);
            return new RedirectResponse($absoluteUrl);
        }

        Util::addStyle(Application::APP_ID, 'bestand');
        return new TemplateResponse(Application::APP_ID, 'main');
    }
}
