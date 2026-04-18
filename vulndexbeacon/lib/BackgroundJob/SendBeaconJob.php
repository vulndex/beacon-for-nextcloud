<?php
declare(strict_types=1);

namespace OCA\VulnDexBeacon\BackgroundJob;

use OCA\VulnDexBeacon\Service\BeaconService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

/**
 * Represents a timed job that periodically sends beacon data using the BeaconService.
 *
 * The job is scheduled to execute at a fixed interval of 10800 seconds (3 hours).
 * It leverages the BeaconService to perform the actual sending of the beacon.
 *
 * This class extends the TimedJob base class, which provides the mechanism for scheduling and running timed jobs.
 *
 * @property BeaconService $beaconService Instance of the service responsible for sending beacon data.
 *
 * @method void __construct(ITimeFactory $time, BeaconService $beaconService) Initializes the job with a time factory and the beacon service.
 * @method void run(mixed $argument) Executes the logic for sending beacon data.
 */
class SendBeaconJob extends TimedJob {


	private BeaconService $beaconService;

	/**
	 * Initializes an instance of the timed job, configuring its execution interval and dependencies.
	 *
	 * The job is configured to run every 10800 seconds (3 hours). It uses the BeaconService
	 * to handle specific operations related to beacon data.
	 *
	 * @param ITimeFactory $time An instance of the time factory used for time-based operations.
	 * @param BeaconService $beaconService The service responsible for managing beacon data operations.
	 *
	 * @return void
	 */
	public function __construct(ITimeFactory $time, BeaconService $beaconService) {
		parent::__construct($time);

		$this->setInterval(10800);
		$this->beaconService = $beaconService;


	}


	/**
	 * Executes the run method to perform the designated action.
	 *
	 * @param mixed $argument The input parameter used for executing the method.
	 * @return void
	 */
	protected function run($argument): void {
		$this->beaconService->sendBeacon();
	}
}
