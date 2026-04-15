<?php
declare(strict_types=1);
namespace OCA\VulnDexBeacon\Settings;


use OCA\VulnDexBeacon\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings
{
	public function __construct(
		private readonly IInitialState $initialStateService,
		private IL10N                  $l,
		private readonly IAppConfig    $appConfig,
	)
	{
	}

	public function getForm(): TemplateResponse
	{

		$apiKeySet = $this->appConfig->getValueString(Application::APP_ID, 'api_key', '') !== '';
		\OCP\Util::addScript(Application::APP_ID, Application::APP_ID . '-adminSettings');

		return new TemplateResponse(Application::APP_ID, 'admin', [
			'apiKeySet' => $apiKeySet,
			'lastSend' => $this->getLastSend(),
		]);


	}

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


	public function getSection(): string
	{
		return Application::APP_ID;
	}

	public function getPriority(): int
	{
		return 10;
	}
}
