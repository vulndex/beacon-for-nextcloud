<?php
declare(strict_types=1);

namespace OCA\VulnDexBeacon\Sections;

use OCA\VulnDexBeacon\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/**
 * Represents the AdminSection of an application, implementing the IIconSection interface.
 * Provides methods to retrieve section-specific details such as ID, name, priority, and icon.
 */
class AdminSection implements IIconSection {


	/**
	 *
	 * @param IL10N $l Localization service instance.
	 * @param IURLGenerator $urlGenerator URL generation service instance.
	 * @return void
	 */
	public function __construct(
		private IL10N $l,
		private IURLGenerator $urlGenerator,
	) {
	}

	/**
	 * Retrieves the application ID.
	 *
	 * @return string The ID of the application.
	 */
	public function getID(): string {
		return Application::APP_ID;
	}

	/**
	 * Retrieves the name of the beacon.
	 *
	 * @return string Translated name of the beacon.
	 */
	public function getName(): string {
		return $this->l->t('VulnDex Beacon');
	}

	/**
	 * Retrieves the priority value.
	 *
	 * @return int The priority value.
	 */
	public function getPriority(): int {
		return 80;
	}

	/**
	 *
	 * @return string The URL path to the application's icon image.
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
	}
}
