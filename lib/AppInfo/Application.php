<?php

namespace OCA\Bestand\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'bestand';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
