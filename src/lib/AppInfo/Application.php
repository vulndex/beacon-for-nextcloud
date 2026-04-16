<?php

declare(strict_types=1);

namespace OCA\VulnDexBeacon\AppInfo;

use OCA\VulnDexBeacon\BackgroundJob\SendBeaconJob;
use OCA\VulnDexBeacon\Service\BeaconService;
use OCP\App\IAppManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IAppConfig;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Represents the main application class that extends the base application functionality
 * and implements the IBootstrap interface.
 */
class Application extends App implements IBootstrap {
	public const APP_ID = 'vulndexbeacon';

	public const ENDPOINT_URL = 'https://api.vulndex.at/beacon/nextcloud';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);

	}

	/**
	 * Generates a unique node identifier.
	 *
	 * @return string A string representing a unique node ID, generated using random bytes or a fallback to a prefixed unique ID.
	 */
	private static function generateNodeId(): string {
		try {
			return bin2hex(random_bytes(16));
		} catch (\Throwable $e) {
			return uniqid('vulndex_', true);
		}
	}

	/**
	 * Registers services and dependencies within the provided registration context.
	 *
	 * @param IRegistrationContext $context The registration context used to register the services.
	 * @return void
	 */
	public function register(IRegistrationContext $context): void {

		$context->registerService(BeaconService::class, function ($c) {
			return new BeaconService(
				$c->get(\OCP\IAppConfig::class),
				$c->get(\OCP\IRequest::class),
				$c->get(\OCP\IURLGenerator::class),
				$c->get(\OCP\Http\Client\IClientService::class),
				$c->get(LoggerInterface::class),
				$c->get(IAppManager::class),
				$c->get(IL10N::class)
			);
		});




	}

	/**
	 * Boots the application by setting up necessary configuration values if they are not already defined.
	 *
	 * @param IBootContext $context The context used during the boot process, providing access to the server container and configuration.
	 * @return void
	 */
	public function boot(IBootContext $context): void {

		$appConfig = $context->getServerContainer()->get(IAppConfig::class);

		if (!$appConfig->hasKey(self::APP_ID, 'node_id')) {
			$appConfig->setValueString(self::APP_ID, 'node_id', self::generateNodeId());
		}

	}
}
