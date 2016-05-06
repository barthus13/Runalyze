<?php

namespace Runalyze\Calculation\JD;

use PDO;
use Runalyze\Model\Factory;
use Runalyze\Model\Activity;
use Runalyze\Model\RaceResult;
use Runalyze\Configuration;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-11-30 at 11:21:45.
 */
class VDOTCorrectorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;
	
	protected $runningSportId;
	
	protected $runningRaceTypeId;

	protected function setUp() {
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('DELETE FROM `runalyze_training`');
		$this->PDO->exec('DELETE FROM `runalyze_raceresult`');
		$this->PDO->exec("DELETE FROM `runalyze_conf` WHERE `key` = 'RUNNINGSPORT'");
		$this->PDO->exec("INSERT INTO runalyze_conf (`category`, `key`, `value`, `accountid`) VALUES ('general', 'RUNNINGSPORT', 3, 1)");
		Configuration::loadAll(1);
		$this->runningSportId = Configuration::General()->runningSport();
		VDOTCorrector::setGlobalFactor(1);
	}

	protected function tearDown() {
		VDOTCorrector::setGlobalFactor(1);
		$this->PDO->exec('DELETE FROM `runalyze_training`');
		$this->PDO->exec('DELETE FROM `runalyze_raceresult`');
		$this->PDO->exec('DELETE FROM `runalyze_conf`');
		Configuration::loadAll(1);
	}

	public function testSimpleFactor() {
		$Corrector = new VDOTCorrector(0.95);

		$this->assertEquals(0.95, $Corrector->factor());
	}

	public function testApply() {
		$Corrector = new VDOTCorrector(0.9);
		$VDOT = new VDOT(50);
		$Corrector->applyTo($VDOT);

		$this->assertEquals(45, $VDOT->value());
	}

	public function testGlobalFactor() {
		VDOTCorrector::setGlobalFactor(0.5);
		$Corrector = new VDOTCorrector;

		$this->assertEquals(0.5, $Corrector->factor());
	}

	public function testFromActivity() {
		$Corrector = new VDOTCorrector;
		$Corrector->fromActivity(
			$Activity = new Activity\Entity(array(
				Activity\Entity::VDOT_BY_TIME => 45,
				Activity\Entity::VDOT => 50
			))
		);

		$this->assertEquals(0.9, $Corrector->factor());
	}

	public function testFromEmptyActivity() {
		$Corrector = new VDOTCorrector;
		$Corrector->fromActivity(
			$Activity = new Activity\Entity(array(
				Activity\Entity::VDOT_BY_TIME => 45
			))
		);

		$this->assertEquals(1, $Corrector->factor());
	}

	protected function insert($vdot, $vdot_by_time, $accountid = 0, $typeid = 0, $sportid, $s = 50) {
		$Activity = $this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`vdot`, `vdot_by_time`, `sportid`, `typeid`, `accountid`, `s`) VALUES('.$vdot.','.$vdot_by_time.','.$sportid.','.$typeid.', '.$accountid.','.$s.')');
		$activityId = $this->PDO->lastInsertId();
		$RaceResult = new RaceResult\Entity(array(
			RaceResult\Entity::OFFICIAL_TIME => $s,
			RaceResult\Entity::OFFICIAL_DISTANCE => '10',
			RaceResult\Entity::ACTIVITY_ID => $activityId));
		$RaceResultInserter = new RaceResult\Inserter($this->PDO, $RaceResult);
		$RaceResultInserter->setAccountID($accountid);
		$RaceResultInserter->insert();
	}
	
	public function testFromDatabase() {
		$runningSportId = $this->runningSportId;
		$AccountNullRunningRaceTypeId = 3;
		$this->insert(0, 90, 1, $AccountNullRunningRaceTypeId, $runningSportId);
		$this->insert(50, 25, 1, $AccountNullRunningRaceTypeId, $runningSportId);
		$this->insert(50, 45, 1, $AccountNullRunningRaceTypeId, $runningSportId);
		$this->insert(100, 80, 1, $AccountNullRunningRaceTypeId, $runningSportId);
		$this->insert(90, 90, 0, $AccountNullRunningRaceTypeId, $runningSportId);

		$Corrector = new VDOTCorrector;
		$Corrector->fromDatabase($this->PDO, 1, $runningSportId);

		$this->assertEquals(0.9, $Corrector->factor());
	}

}
