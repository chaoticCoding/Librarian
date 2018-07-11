<?php
/**
 * \util\chronograph
 */
namespace util
{

    /**
     * Class chronograph
     * @package util
     */
    class chronograph
    {
        /**  */
        const DB_TIME_FORMAT = 'Y-m-d H:i:s';

        /**  */
        const DB_TIME_FORMAT_ZONE = 'Y-m-d H:i:s T';

        /**  */
        const DB_TIME_FORMAT_Midnight = 'Y-m-d 23:59:59';

        /**  */
        const DB_TIME_FORMAT_ISO8601 = 'Y-m-d\TH:i:s\Z';

        /**  */
        const NON_TZ_TIME_FORMAT = 'm/d/Y g:i:s A';

        /**
         * $dateA: String representing Date and Time
         * $dateB: String representing Date and Time
         * Note: $dateA and $dateB should be in the same timezone
         *
         * If dateA > dateB, return 1
         * If dateA < dateB, return -1
         * If dateA == dateB, return 0
         * Else, return -1
         ***/
        public static function compareDateTime($dateA, $dateB)
        {
            $timeA = strtotime($dateA);
            $timeB = strtotime($dateB);

            $result = -1;
            if ($timeA > $timeB) {
                $result = 1;
            } elseif ($timeA < $timeB) {
                $result = -1;
            } elseif ($timeA == $timeB) {
                $result = 0;
            }

            return $result;
        }

        /**
         * Returns the DateTime object of a given DateTime string in UTC.
         *
         * @param string $date | representing Date and Time in UTC
         * @param string $timeZone | The timezone of the returned DateTime object
         *
         * @return \DateTime
         */
        public static function getDateTimeInUTC($date, $timeZone)
        {
            $dateTime = \DateTime::createFromFormat(self::NON_TZ_TIME_FORMAT, $date, new \DateTimeZone($timeZone));

            return $dateTime->setTimeZone(new \DateTimeZone("UTC"));
        }


        /**
         * Returns the timezone adjusted DateTime object of a given UTC DateTime string.
         *
         * @param string $date | representing Date and Time in UTC
         * @param string $timeZone | The timezone of the returned DateTime object
         *
         * @return \DateTime
         */
        public static function getAdjustedDateTimeFromUTC($date, $timeZone)
        {
            $dateTime = new \DateTime($date, new \DateTimeZone("UTC"));

            if(!$timeZone instanceof \DateTimeZone) {
                $tz = new \DateTimeZone($timeZone);
            } else {
                $tz = $timeZone;
            }
            return $dateTime->setTimeZone($tz);
        }

        /**
         * @param string|int       $timestamp
         * @param string           $currentTimeZone
         * @param string           $desiredTimeZone
         * @param string           $format
         *
         * @return string
         */
        public static function convertTimeZoneOfTimestamp($timestamp, $currentTimeZone, $desiredTimeZone, $format = self::DB_TIME_FORMAT)
        {
            $date = new \DateTime($timestamp, new \DateTimeZone($currentTimeZone));
            $date->setTimezone(new \DateTimeZone($desiredTimeZone));

            return $date->format($format);
        }

        /**
         * returns date/time object with provided format, if no timstamp or format is provided uses now or default sqlDB format
         *
         * @param null|string $timeStamp
         * @param string      $format
         *
         * @return string date/time object
         */
        public static function prepareTime($timeStamp = NULL, $format = self::DB_TIME_FORMAT)
        {
            $time = new \DateTime();

            if (is_null($format)) {
                $format = self::DB_TIME_FORMAT;
            }

            if (!is_null($timeStamp)) {
                $time->setTimestamp(strtotime($timeStamp));
            }

            return $time->format($format); // date($format, strtotime($timeStamp));
        }

        /**
         * @param        $date
         *
         * @return false|string
         */
        public static function setDataWithMidnight($date)
        {
            $UTCDate = date(self::DB_TIME_FORMAT_Midnight, strtotime($date));

            return $UTCDate;
        }

        /**
         * @param $date
         *
         * @return bool
         */
        public static function dateNullOrZero($date)
        {
            if ((!$date) || $date == '0000-00-00' || $date == '1969-12-31' || $date == '0000-00-00 00:00:00') {
                return TRUE;
            }

            return FALSE;
        }

        /**
         * @param $seconds
         *
         * @return string
         */
        public static function getDurationStringFromSeconds($seconds)
        {
            $durationInDays = floor(($seconds) / 86400);
            $durationInHours = floor(($seconds) / 3600) - ($durationInDays * 24);
            $durationInMinutes = round(($seconds) / 60) - ($durationInHours * 60) - ($durationInDays * 1440);

            $durationArray = [];

            switch ($durationInDays) {
                case 0:
                    break;
                case 1:
                    $durationArray[] = $durationInDays . " day";
                    break;
                default:
                    $durationArray[] = $durationInDays . " days";
            }

            switch ($durationInHours) {
                case 0:
                    break;
                case 1:
                    $durationArray[] = $durationInHours . " hour";
                    break;
                default:
                    $durationArray[] = $durationInHours . " hours";
            }

            switch ($durationInMinutes) {
                case 0:
                    break;
                case 1:
                    $durationArray[] = $durationInMinutes . " minute";
                    break;
                default:
                    $durationArray[] = $durationInMinutes . " minutes";
            }

            $duration = implode(", ", $durationArray);

            return $duration;
        }

        /**
         * Returns true if either range overlaps the other
         *
         * @param \DateTime $rangeOne_From
         * @param \DateTime $rangeOne_To
         *
         * @param \DateTime $rangeTwo_From
         * @param \DateTime $rangeTwo_To
         *
         * @return bool
         */
        public static function dtRangeBoundaryDetection(\DateTime $rangeOne_From, \DateTime $rangeOne_To, \DateTime $rangeTwo_From, \DateTime $rangeTwo_To) : bool
        {
            // range one starts inside range two
            if(self::dtInRange($rangeOne_From, $rangeTwo_From, $rangeTwo_To)) {
                return true;

            }

            // range one ends inside range two
            if(self::dtInRange($rangeOne_To, $rangeTwo_From, $rangeTwo_To)) {
                return true;

            }

            // range two starts inside range one
            if(self::dtInRange($rangeTwo_From, $rangeOne_From, $rangeOne_To)) {
                return true;

            }

            // range two ends inside range one
            if(self::dtInRange($rangeTwo_To, $rangeOne_From, $rangeOne_To)) {
                return true;

            }

            return false;
        }

        /**
         * returns true if $dt between $from and $to
         * @param \DateTime $dt
         *
         * @param \DateTime $From
         * @param \DateTime $To
         *
         * @return bool
         */
        public static function dtInRange(\DateTime $dt, \DateTime $From, \DateTime $To)
        {
            if(($dt >= $From) && ($dt <= $To)){
                return true;
            }

            return false;
        }

        /**
         * @param \DateTime     $dtA
         * @param \DateTime     $dtB
         *
         * @return \DateTime
         */
        public static function dtFloor (\DateTime $dtA, \DateTime $dtB)
        {
            $floor = $dtA;
            if($floor > $dtB) {
                $floor = $dtB;
            }

            return $floor;
        }

        /**
         * @param \DateTime     $dtA
         * @param \DateTime     $dtB
         *
         * @return \DateTime
         */
        public static function dtCeil (\DateTime $dtA, \DateTime $dtB)
        {
            $ceil = $dtA;
            if($ceil < $dtB) {
                $ceil = $dtB;
            }

            return $ceil;
        }
        /**
         * @param null $selected
         * @param bool $disabled
         *
         * @return string
         */
        public static function renderHours($selected = null, $disabled = false)
        {
            $html = '';
            if(\DEBUGGING_ENABLED == true) {
                $html .= sprintf('<!--selected: %s | disabled: %d -->', $selected, (int) $disabled);
            }
            $html .= '<select name="field-timeEndHour" class="form-control" id="field-timeEndHour"' . ($disabled == true ? ' disabled' : ''). '>';

            for ($hour = 1; $hour <= 12; $hour++) {
                $html .= sprintf('<option value="%02d"%s>%02d</option>', $hour, $selected == $hour ? ' selected' : '', $hour);
            }

            $html .=  '</select>';

            return $html;
        }

        /**
         * @param $timezone
         *
         * @return false|int|string
         */
        public static function getPrettyTimezoneName($timezone)
        {
            return array_search($timezone, self::getTimeZones());
        }

        /**
         * @return array
         */
        public static function getPrimaryTimeZones()
        {
            return [
                '(UTC-05:00) Eastern Time (US &amp; Canada)'                  => 'US/Eastern',
                '(UTC-06:00) Central Time (US &amp; Canada)'                  => 'US/Central',
                '(UTC-07:00) Mountain Time (US &amp; Canada)'                 => 'US/Mountain',
                '(UTC-08:00) Pacific Time (US &amp; Canada)'                  => 'America/Los_Angeles',
                '(UTC-09:00) Alaska'                                          => 'US/Alaska',
                '(UTC+00:00) UTC'                                             => 'UTC',
                '(UTC-11:00) Midway Island'                                   => 'Pacific/Midway',
                '(UTC-10:00) Hawaii'                                          => 'Pacific/Honolulu',
                '(UTC-04:00) Atlantic Time (Canada)'                          => 'Canada/Atlantic',
                '(UTC-03:00) Greenland'                                       => 'America/Godthab',
                '(UTC-02:00) Mid-Atlantic'                                    => 'America/Noronha',
                '(UTC-01:00) Cape Verde Is.'                                  => 'Atlantic/Cape_Verde',
                '(UTC+01:00) Brussels, Copenhagen, Madrid, Paris'             => 'Europe/Brussels',
                '(UTC+02:00) Athens, Bucharest'                               => 'Europe/Athens',
                '(UTC+03:00) Baghdad'                                         => 'Asia/Baghdad',
                '(UTC+03:30) Tehran'                                          => 'Asia/Tehran',
                '(UTC+04:00) Moscow, St. Petersburg'                          => 'Europe/Moscow',
                '(UTC+04:30) Kabul'                                           => 'Asia/Kabul',
                '(UTC+05:00) Karachi, Islamabad'                              => 'Asia/Karachi',
                '(UTC+05:30) Mumbai, New Delhi, Chennai, Sri Jayawardenepura' => 'Asia/Calcutta',
                '(UTC+05:45) Kathmandu'                                       => 'Asia/Katmandu',
                '(UTC+06:00) Almaty'                                          => 'Asia/Almaty',
                '(UTC+06:30) Rangoon'                                         => 'Asia/Rangoon',
                '(UTC+07:00) Bangkok, Hanoi, Jakarta'                         => 'Asia/Bangkok',
                '(UTC+08:00) Beijing, Hong Kong, Chongqing'                   => 'Asia/Hong_Kong',
                '(UTC+08:00) Taipei'                                          => 'Asia/Taipei',
                '(UTC+09:00) Osaka, Sapporo, Tokyo'                           => 'Asia/Tokyo',
                '(UTC+09:30) Darwin'                                          => 'Australia/Darwin',
                '(UTC+10:00) Canberra, Melbourne, Sydney'                     => 'Australia/Sydney',
                '(UTC+11:00) Vladivostok'                                     => 'Asia/Vladivostok',
                '(UTC+12:00) Fiji'                                            => 'Pacific/Fiji',
                '(UTC+13:00) Samoa'                                           => 'Pacific/Samoa'
            ];
        }

        /**
         * @param string $selectedTZ
         * @param bool   $disabled
         *
         * @return string
         */
        public static function renderPrimaryTimeZones($selectedTZ = 'EST', $disabled = false)
        {
            $html = '';
            if(\DEBUGGING_ENABLED == true) {
                $html .= sprintf('<!--selected: %s | disabled: %d -->', $selectedTZ, (int) $disabled);
            }
            $html .= '<select name="field-timeZone" class="form-control" id="field-timeZone"' . ($disabled == true ? ' disabled' : '') . '>';

            foreach (self::getPrimaryTimeZones() as $zoneDesc => $zone) {
                $TZShort = self::getAdjustedDateTimeFromUTC(date(self::DB_TIME_FORMAT), $zone)->format("T");

                $html .= sprintf('<option value="%s" %s>%s</option>', $zone, ($TZShort == $selectedTZ || $zone == $selectedTZ) ? "selected" : "", $TZShort);
            }
            $html .= '</select>';

            return $html;
        }

        /**
         * @return array
         */
        public static function getTimeZones()
        {
            return [
                '(UTC-05:00) Eastern Time (US &amp; Canada)'                    => 'US/Eastern',
                '(UTC-06:00) Central Time (US &amp; Canada)'                    => 'US/Central',
                '(UTC-07:00) Mountain Time (US &amp; Canada)'                   => 'US/Mountain',
                '(UTC-08:00) Pacific Time (US &amp; Canada)'                    => 'America/Los_Angeles',
                '(UTC-09:00) Alaska'                                            => 'US/Alaska',
                '(UTC+00:00) UTC'                                               => 'UTC',
                '(UTC-11:00) Midway Island'                                     => 'Pacific/Midway',
                '(UTC-10:00) Hawaii'                                            => 'Pacific/Honolulu',
                '(UTC-08:00) Tijuana'                                           => 'America/Tijuana',
                '(UTC-07:00) Arizona'                                           => 'US/Arizona',
                '(UTC-07:00) Chihuahua, La Paz, Mazatlan'                       => 'America/Chihuahua',
                '(UTC-06:00) Central America'                                   => 'America/Managua',
                '(UTC-06:00) Guadalajara, Mexico City, Monterrey'               => 'America/Mexico_City',
                '(UTC-06:00) Saskatchewan'                                      => 'Canada/Saskatchewan',
                '(UTC-05:00) Bogota, Lima, Quito'                               => 'America/Bogota',
                '(UTC-05:00) Indiana (East)'                                    => 'US/East-Indiana',
                '(UTC-04:30) Caracas'                                           => 'America/Caracas',
                '(UTC-04:00) Atlantic Time (Canada)'                            => 'Canada/Atlantic',
                '(UTC-04:00) Georgetown, La Paz'                                => 'America/La_Paz',
                '(UTC-04:00) Santiago'                                          => 'America/Santiago',
                '(UTC-03:30) Newfoundland'                                      => 'Canada/Newfoundland',
                '(UTC-03:00) Brasilia'                                          => 'America/Sao_Paulo',
                '(UTC-03:00) Buenos Aires'                                      => 'America/Argentina/Buenos_Aires',
                '(UTC-03:00) Greenland'                                         => 'America/Godthab',
                '(UTC-02:00) Mid-Atlantic'                                      => 'America/Noronha',
                '(UTC-01:00) Azores'                                            => 'Atlantic/Azores',
                '(UTC-01:00) Cape Verde Is.'                                    => 'Atlantic/Cape_Verde',
                '(UTC+00:00) Casablanca'                                        => 'Africa/Casablanca',
                '(UTC+00:00) Edinburgh, Lisbon, London'                         => 'Europe/London',
                '(UTC+00:00) Greenwich Mean Time : Dublin'                      => 'Etc/Greenwich',
                '(UTC+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna'  => 'Europe/Amsterdam',
                '(UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague' => 'Europe/Belgrade',
                '(UTC+01:00) Brussels, Copenhagen, Madrid, Paris'               => 'Europe/Brussels',
                '(UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb'                  => 'Europe/Sarajevo',
                '(UTC+01:00) West Central Africa'                               => 'Africa/Lagos',
                '(UTC+02:00) Athens, Bucharest'                                 => 'Europe/Athens',
                '(UTC+02:00) Cairo'                                             => 'Africa/Cairo',
                '(UTC+02:00) Harare'                                            => 'Africa/Harare',
                '(UTC+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius'     => 'Europe/Helsinki',
                '(UTC+02:00) Istanbul'                                          => 'Europe/Istanbul',
                '(UTC+02:00) Jerusalem'                                         => 'Asia/Jerusalem',
                '(UTC+02:00) Pretoria'                                          => 'Africa/Johannesburg',
                '(UTC+03:00) Baghdad'                                           => 'Asia/Baghdad',
                '(UTC+03:00) Kuwait, Riyadh'                                    => 'Asia/Kuwait',
                '(UTC+03:00) Minsk'                                             => 'Europe/Minsk',
                '(UTC+03:00) Nairobi'                                           => 'Africa/Nairobi',
                '(UTC+03:30) Tehran'                                            => 'Asia/Tehran',
                '(UTC+04:00) Abu Dhabi, Muscat'                                 => 'Asia/Muscat',
                '(UTC+04:00) Baku'                                              => 'Asia/Baku',
                '(UTC+04:00) Moscow, St. Petersburg'                            => 'Europe/Moscow',
                '(UTC+04:00) Tbilisi'                                           => 'Asia/Tbilisi',
                '(UTC+04:00) Yerevan'                                           => 'Asia/Yerevan',
                '(UTC+04:30) Kabul'                                             => 'Asia/Kabul',
                '(UTC+05:00) Karachi, Islamabad'                                => 'Asia/Karachi',
                '(UTC+05:00) Tashkent'                                          => 'Asia/Tashkent',
                '(UTC+05:30) Mumbai, New Delhi, Chennai, Sri Jayawardenepura'   => 'Asia/Calcutta',
                '(UTC+05:30) Kolkata'                                           => 'Asia/Kolkata',
                '(UTC+05:45) Kathmandu'                                         => 'Asia/Katmandu',
                '(UTC+06:00) Almaty'                                            => 'Asia/Almaty',
                '(UTC+06:00) Astana, Dhaka'                                     => 'Asia/Dhaka',
                '(UTC+06:30) Rangoon'                                           => 'Asia/Rangoon',
                '(UTC+07:00) Bangkok, Hanoi, Jakarta'                           => 'Asia/Bangkok',
                '(UTC+08:00) Beijing, Hong Kong, Chongqing'                     => 'Asia/Hong_Kong',
                '(UTC+08:00) Kuala Lumpur, Singapore'                           => 'Asia/Kuala_Lumpur',
                '(UTC+08:00) Perth'                                             => 'Australia/Perth',
                '(UTC+08:00) Taipei'                                            => 'Asia/Taipei',
                '(UTC+08:00) Ulaanbaatar'                                       => 'Asia/Ulan_Bator',
                '(UTC+09:00) Irkutsk'                                           => 'Asia/Irkutsk',
                '(UTC+09:00) Osaka, Sapporo, Tokyo'                             => 'Asia/Tokyo',
                '(UTC+09:00) Seoul'                                             => 'Asia/Seoul',
                '(UTC+09:30) Adelaide'                                          => 'Australia/Adelaide',
                '(UTC+09:30) Darwin'                                            => 'Australia/Darwin',
                '(UTC+10:00) Brisbane'                                          => 'Australia/Brisbane',
                '(UTC+10:00) Canberra, Melbourne, Sydney'                       => 'Australia/Sydney',
                '(UTC+10:00) Guam, Port Moresby'                                => 'Pacific/Guam',
                '(UTC+10:00) Hobart'                                            => 'Australia/Hobart',
                '(UTC+11:00) Vladivostok'                                       => 'Asia/Vladivostok',
                '(UTC+12:00) Auckland, Wellington'                              => 'Pacific/Auckland',
                '(UTC+12:00) Fiji'                                              => 'Pacific/Fiji',
                '(UTC+12:00) UTC+12'                                            => 'Pacific/Kwajalein',
                '(UTC+12:00) Kamchatka'                                         => 'Asia/Kamchatka',
                '(UTC+12:00) Marshall Is.'                                      => 'Pacific/Fiji',
                '(UTC+13:00) Nuku\'alofa'                                       => 'Pacific/Tongatapu',
                '(UTC+13:00) Samoa'                                             => 'Pacific/Samoa'
            ];
        }
    }
}