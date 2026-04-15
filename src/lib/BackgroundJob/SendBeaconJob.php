<?php
declare(strict_types=1);

namespace OCA\VulnDexBeacon\BackgroundJob;

use OCA\VulnDexBeacon\Service\BeaconService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class SendBeaconJob extends TimedJob {


	private BeaconService $beaconService;
	public function __construct(ITimeFactory $time, BeaconService $beaconService) {
		parent::__construct($time);

		$this->setInterval(10800);
		$this->beaconService = $beaconService;


	}



	protected function run($argument): void {
		$this->beaconService->sendBeacon();
	}
}
