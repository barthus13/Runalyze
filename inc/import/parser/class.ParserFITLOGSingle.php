<?php
/**
 * This file contains class::ParserFITLOGSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;
use Runalyze\Import\Exception\UnsupportedFileException;

/**
 * Parser for FITLOG files from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITLOGSingle extends ParserAbstractSingleXML {
	/** @var bool */
	protected $HasRoute = true;

	/** @var string */
	protected $StarttimeString = '';

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectFITLOG()) {
			$this->parseGeneralValues();
            $this->parseLaps();
			$this->parseTrack();
			$this->parsePauses();
			$this->applyPauses();
			$this->finishLaps();
			$this->setGPSarrays();
		} else {
			$this->throwNoFITLOGError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectFITLOG() {
		return property_exists($this->XML, 'Duration');
	}

	/**
	 * Add error: incorrect file
	 * @throws \Runalyze\Import\Exception\UnsupportedFileException
	 */
	protected function throwNoFITLOGError() {
		throw new UnsupportedFileException('Given XML object is not from SportTracks. &lt;Duration&gt;-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
	    $this->StarttimeString = (string)$this->XML['StartTime'];
		$this->setTimestampAndTimezoneOffsetWithUtcFixFrom($this->StarttimeString);

		if (!empty($this->XML['categoryName']))
			$this->guessSportID( (string)$this->XML['categoryName'] );
		else
			$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (!empty($this->XML->Duration['TotalSeconds']))
			$this->TrainingObject->setTimeInSeconds(round((double)$this->XML->Duration['TotalSeconds']));

		if (!empty($this->XML->Distance['TotalMeters']))
			$this->TrainingObject->setDistance(round((double)$this->XML->Distance['TotalMeters'])/1000);

		if (!empty($this->XML->Calories['TotalCal']))
			$this->TrainingObject->setCalories((int)$this->XML->Calories['TotalCal']);

		if (!empty($this->XML->Location['Name']))
			$this->TrainingObject->setRoute((string)$this->XML->Location['Name']);

		if (!empty($this->XML->Weather['Temp']))
			$this->TrainingObject->setTemperature((int)$this->XML->Weather['Temp']);

		if (!empty($this->XML->HeartRateMaximumBPM))
			$this->TrainingObject->setPulseMax((int)$this->XML->HeartRateMaximumBPM);

		if (!empty($this->XML->HeartRateAverageBPM))
			$this->TrainingObject->setPulseAvg((int)$this->XML->HeartRateAverageBPM);
	}

	/**
	 * Parse all log entries
	 */
	protected function parseTrack() {
		if (isset($this->XML->Track->pt))
			foreach ($this->XML->Track->pt as $Point)
				$this->parseTrackpoint($Point);
	}

	/**
	 * Parse trackpoint
	 * @param SimpleXMLElement $Point
	 */
	protected function parseTrackpoint($Point) {
		if (!empty($Point['lat'])) {
			$lat  = round((double)$Point['lat'], 7);
			$lon  = round((double)$Point['lon'], 7);
			$dist = empty($this->gps['latitude'])
					? 0
					: round(Runalyze\Model\Route\Entity::gpsDistance($lat, $lon, end($this->gps['latitude']), end($this->gps['longitude'])), ParserAbstract::DISTANCE_PRECISION);
		} elseif (count($this->gps['latitude'])) {
			$lat  = end($this->gps['latitude']);
			$lon  = end($this->gps['longitude']);
			$dist = 0;
		} else {
			$this->HasRoute = false;
		}

		if ($this->HasRoute) {
			$this->gps['km'][]        = empty($this->gps['km']) ? $dist : $dist + end($this->gps['km']);
			$this->gps['latitude'][]  = $lat;
			$this->gps['longitude'][] = $lon;
		}

		$this->gps['time_in_s'][] = (int)$Point['tm'];
		$this->gps['altitude'][]  = (!empty($Point['ele'])) ? (int)$Point['ele'] : 0;
		$this->gps['heartrate'][] = (!empty($Point['hr'])) ? (int)$Point['hr'] : 0;
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (!isset($this->XML->Laps))
			return;

		$Distance = 0;
		$Calories = 0;

		foreach ($this->XML->Laps->children() as $Lap) {
			$LapDist = (!empty($Lap->Distance['TotalMeters'])) ? ((int)$Lap->Distance['TotalMeters'])/1000 : 0;
			$Distance += $LapDist;

			if (!empty($Lap['DurationSeconds'])) {
				$this->TrainingObject->Splits()->addSplit($LapDist, (int)$Lap['DurationSeconds']);
			}

			if (!empty($Lap->Distance['TotalCal']))
				$Calories += (int)$Lap->Calories['TotalCal'];
		}

		if ($Distance > 0)
			$this->TrainingObject->setDistance($Distance);
		if ($Calories > 0)
			$this->TrainingObject->setCalories($Calories);

		if ($Distance == 0 && !empty($this->gps['km'])) {
		    $this->TrainingObject->Splits()->fillDistancesFromArray($this->gps['time_in_s'], $this->gps['km']);
        }
	}

	protected function parsePauses() {
	    if (isset($this->XML->TrackClock)) {
	        foreach ($this->XML->TrackClock->children() as $Pause) {
                $this->pausesToApply[] = array(
                    'time' => strtotime((string)$Pause['StartTime']) - strtotime($this->StarttimeString),
                    'duration' => (strtotime((string)$Pause['EndTime']) - strtotime((string)$Pause['StartTime']))
                );
            }

            $this->TrainingObject->setElapsedTime(end($this->gps['time_in_s']) - $this->gps['time_in_s'][0]);
        }
    }

    protected function finishLaps() {
        if ($this->TrainingObject->Splits()->totalDistance() == 0 && !empty($this->gps['km'])) {
            $this->TrainingObject->Splits()->fillDistancesFromArray($this->gps['time_in_s'], $this->gps['km']);
        }
    }
}
