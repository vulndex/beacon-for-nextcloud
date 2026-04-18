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

/**
 * Service responsible for handling beacon-related functionalities such as
 * data aggregation, API communication, and logging data transmission status.
 * Provides methods to collect client environment data, generate payloads,
 * and send beacons to external services.
 */
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


	/**
	 * Retrieves the API key from the configuration.
	 *
	 * @return string Returns the API key as a string. If no API key is set, returns an empty string.
	 */
	public function getApiKey(): string
	{
		return (string)$this->config->getValueString(Application::APP_ID, 'api_key', '');
	}


	/**
	 * Collects data to be included in the payload for sending to a remote endpoint.
	 * The data contains metadata about the server, installed applications, and a timestamp.
	 *
	 * @return array Returns an associative array containing:
	 * - 'sent_at' (string): The timestamp in ISO 8601 format when the data was collected.
	 * - 'node_id' (string): A unique identifier for the node.
	 * - 'instance' (array): Information about the server instance.
	 * - 'apps' (array): A list of installed applications and their details.
	 */
	public function collectData(): array
	{
		return [
			'sent_at'  => gmdate('c'),
			'node_id'  => $this->config->getValueString(Application::APP_ID, 'node_id', ''),
			'instance' => $this->getServerInfo(),
			'apps'     => $this->getInstalledApps(),
		];
	}


	/**
	 * Sends a beacon by creating and dispatching a payload to a predefined endpoint.
	 * Utilizes an API key for authentication and processes the server's response.
	 * In case of an error, it logs the details and retrieves the last send attempt.
	 *
	 * @return array Returns an associative array containing:
	 * - 'success' (bool): Indicates if*/
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


	/**
	 * Saves the details of the last send attempt, including the success status, timestamp, and message.
	 *
	 * @param bool $success Indicates whether the last send attempt was successful.
	 * @param string $message A message providing information about the result of the last send attempt.
	 * @return void
	 */
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


	/**
	 * Retrieves server information, including application version, base URL, server name, and IP address.
	 *
	 * @return array Returns an associative array containing:
	 * - 'nextcloud_version' (string): The version of the Nextcloud application.
	 * - 'url' (string): The absolute base URL of the server.
	 * - 'server_name' (string): The host name of the server.
	 * - 'ip_address' (string): The IP address of the server.
	 */
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

	/**
	 * Retrieves a list of installed applications along with their details.
	 * The details include the application ID, name, version, and installation status.
	 *
	 * @return array Returns an array of associative arrays, each containing:
	 * - 'id' (string): The unique identifier of the application.
	 * - 'name' (string): The display name of the application. Defaults to the application ID if not available.
	 * - 'version' (string): The version of the application. Defaults to an empty string if not available.
	 * - 'enabled' (bool): Whether the application is installed and enabled.
	 */
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
