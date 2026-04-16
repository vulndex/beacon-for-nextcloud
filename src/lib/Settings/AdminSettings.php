<?php
declare(strict_types=1);

namespace OCA\VulnDexBeacon\Settings;


use OCA\VulnDexBeacon\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
/**
 * Class responsible for defining and managing the admin settings for the VulnDexBeacon application.
 */

class AdminSettings implements ISettings
{
	public function __construct(
		private readonly IInitialState $initialStateService,
		private IL10N                  $l,
		private readonly IAppConfig    $appConfig,
	)
	{
	}

	/**
	 * Retrieves the form template for the admin settings.
	 *
	 * @return TemplateResponse The response object containing the form template
	 */
	public function getForm(): TemplateResponse
	{

		$apiKeySet = $this->appConfig->getValueString(Application::APP_ID, 'api_key', '') !== '';
		\OCP\Util::addScript(Application::APP_ID, Application::APP_ID . '-adminSettings');

		return new TemplateResponse(Application::APP_ID, 'admin', [
			'apiKeySet' => $apiKeySet,
			'lastSend' => $this->getLastSend(),
		]);


	}

	/**
	 * Retrieves the last send data from the application configuration.
	 *
	 * @return array An associative array containing the following keys:
	 *               - 'timestamp': The timestamp of the last send or null if never sent.
	 *               - 'success': A boolean indicating success of the last send or null if not available.
	 *               - 'message': A localized string indicating the status or an error message.
	 */
	private function getLastSend(): array {
		$data = $this->appConfig->getValueString(Application::APP_ID, 'last_send', '');

		if ($data === '') {
			return [
				'timestamp' => null,
				'success' => null,
				'message' => $this->l->t('Noch nie gesendet'),
			];
		}

		return json_decode($data, true) ?: [
			'timestamp' => null,
			'success' => null,
			'message' => $this->l->t('Fehler beim Laden'),
		];
	}


	/**
	 * Retrieves the section identifier for the application.
	 *
	 * @return string The section identifier.
	 */
	public function getSection(): string
	{
		return Application::APP_ID;
	}

	/**
	 * Retrieves the priority level.
	 *
	 * @return int The priority level.
	 */
	public function getPriority(): int
	{
		return 10;
	}
}
