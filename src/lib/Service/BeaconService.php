<?php

declare(strict_types=1);

namespace OCA\VulnDexBeacon\Service;


use OCA\Circles\Exceptions\JsonException;
use OCA\VulnDexBeacon\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\Http\Client\IClientService;

use OCP\IAppConfig;
use OCP\IL10N;
use OCP\L10N;

use OCP\IRequest;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;

class BeaconService
{


	/** @var IAppConfig */
	private $config;

	/** @var IRequest */
	private $request;

	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IClientService */
	private $clientService;

	/** @var LoggerInterface */
	private $logger;

	/** @var IAppManager */
	private $appManager;

	private IL10N $l;

	public function __construct(
		IAppConfig      $config,
		IRequest        $request,
		IURLGenerator   $urlGenerator,
		IClientService  $clientService,
		LoggerInterface $logger,
		IAppManager     $appManager,
		IL10N           $l1
	)
	{
		$this->config        = $config;
		$this->request       = $request;
		$this->urlGenerator  = $urlGenerator;
		$this->clientService = $clientService;
		$this->logger        = $logger;
		$this->appManager    = $appManager;
		$this->l             = $l1;
	}

	public function getApiKey(): string
	{
		return (string)$this->config->getValueString(Application::APP_ID, 'api_key', '');
	}


	public function collectData(): array
	{
		return [
			'sent_at'  => gmdate('c'),
			'node_id'  => $this->config->getValueString(Application::APP_ID, 'node_id', ''),
			'instance' => $this->getServerInfo(),
			'apps'     => $this->getInstalledApps(),
		];
	}

	public function sendBeacon(): array
	{
		$apiKey = $this->getApiKey();

		if ($apiKey === '')
		{
			return [
				'success' => false,
				'message' => $this->l->t('Kein API-Schlüssel gesetzt.'),
			];
		}

		$payload = $this->collectData();
		$client  = $this->clientService->newClient();


		try
		{
			$response = $client->post(Application::ENDPOINT_URL, [
				'headers' => [
					'Authorization' => 'Bearer ' . $apiKey,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body'    => json_encode($payload),
				'timeout' => 15
			]);

			$statusCode = $response->getStatusCode();
			$success    = $statusCode >= 200 && $statusCode < 300;


			$this->saveLastSend($success, 'HTTP Status: ' . $statusCode);
			try
			{
				$lastSend = json_decode($this->config->getValueString(Application::APP_ID, 'last_send', '{}'), true, 512, JSON_THROW_ON_ERROR);


				$result = [
					'success'        => $success,
					'message'        => 'HTTP Status: ' . $statusCode,
					'lastReport'     => date('d.m.Y H:i:s', $lastSend['timestamp']),
					'status'         => $lastSend['success'],
					'serverResponse' => $lastSend['message']
				];
			}
			catch (JsonException $e)
			{
				$result = [
					'success'        => $success,
					'message'        => 'HTTP Status: ' . $statusCode,
					'lastReport'     => '-',
					'status'         => '-',
					'serverResponse' => '-'
				];
			}


			return $result;
		}
		catch (\Throwable $e)
		{


			$this->saveLastSend(false, $e->getMessage());
			try
			{
				$lastSend = json_decode($this->config->getValueString(Application::APP_ID, 'last_send', '{}'), true, 512, JSON_THROW_ON_ERROR);


				return [
					'success'        => false,
					'message'        => $e->getMessage(),
					'lastReport'     => date('d.m.Y H:i:s', $lastSend['timestamp']),
					'status'         => $lastSend['success'],
					'serverResponse' => $lastSend['message']
				];
			}
			catch (\JsonException $e)
			{
				return [
					'success'        => false,
					'message'        => $e->getMessage(),
					'lastReport'     => '-',
					'status'         => '-',
					'serverResponse' => '-'
				];
			}
		}
	}


	private function saveLastSend(bool $success, string $message): void
	{
		$timestamp = time();
		$data      = [
			'timestamp' => $timestamp,
			'success'   => $success,
			'message'   => $message,
		];

		$this->config->setValueString(Application::APP_ID, 'last_send', json_encode($data));
	}


	private function getServerInfo(): array
	{
		$baseUrl    = $this->urlGenerator->getAbsoluteURL('/');
		$serverName = isset($_SERVER['SERVER_NAME']) ? (string)$_SERVER['SERVER_NAME'] : '';
		$ipAddress  = isset($_SERVER['SERVER_ADDR']) ? (string)$_SERVER['SERVER_ADDR'] : '';
		$ocpVersion = new \OCP\ServerVersion();

		return [
			'nextcloud_version' => $ocpVersion->getVersionString(),
			'url'               => rtrim($baseUrl, '/'),
			'server_name'       => $serverName,
			'ip_address'        => $ipAddress,
		];
	}

	private function getInstalledApps(): array
	{
		$apps = $this->appManager->getInstalledApps();
		sort($apps);

		$appDetails = [];
		foreach ($apps as $app)
		{
			$appInfo      = \OCP\Server::get(\OCP\App\IAppManager::class)->getAppInfo($app);
			$appDetails[] = [
				'id'      => $app,
				'name'    => $appInfo['name'] ?? $app,
				'version' => $appInfo['version'] ?? '',
				'enabled' => $this->appManager->isInstalled($app),
			];
		}

		return $appDetails;
	}
}
