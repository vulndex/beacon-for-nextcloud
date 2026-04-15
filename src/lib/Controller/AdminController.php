<?php

declare(strict_types=1);

namespace OCA\VulnDexBeacon\Controller;

use OC\ForbiddenException;
use OCA\VulnDexBeacon\AppInfo\Application;
use OCA\VulnDexBeacon\Service\BeaconService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\IRequest;

#[OpenAPI(scope: OpenAPI::SCOPE_IGNORE)]
class AdminController extends Controller
{
	/** @var IAppConfig */
	private IAppConfig $config;

	/** @var BeaconService */
	private BeaconService $beaconService;

	private IL10N $l;

	public function __construct(
		string        $AppName,
		IRequest      $request,
		IAppConfig    $config,
		BeaconService $beaconService,
		IL10N         $l,
	)
	{
		parent::__construct($AppName, $request);
		$this->config        = $config;
		$this->beaconService = $beaconService;
		$this->l             = $l;
	}



	public function index(): TemplateResponse
	{


		return new TemplateResponse(Application::APP_ID, 'admin', [
			'apiKeySet' => $this->beaconService->getApiKey() !== ''
		]);
	}


	public function saveApiKey(string $apiKey): DataResponse
	{


		if ($apiKey === '')
		{
			$this->config->setValueString(Application::APP_ID, 'api_key', $apiKey, sensitive: true);

			return new DataResponse([
										'success' => true,
				                        'add'     => false,
				                        'message' => $this->l->t('API-Schlüssel entfernt.'),
									]);

		}


		if (substr_count($apiKey, '.') !== 2)
		{
			return new DataResponse([
										'success' => false,
				                        'message' => $this->l->t('Ungültiges Format für den API-Schlüssel.'),
									]);

		}

		$this->config->setValueString(Application::APP_ID, 'api_key', $apiKey, sensitive: true);

		return new DataResponse([
									'success' => true,
			                        'add'     => true,
			                        'message' => $this->l->t('API-Schlüssel gespeichert.'),
								]);

	}

	public function sendNow(): DataResponse
	{



		return new DataResponse($this->beaconService->sendBeacon());
	}
}
