<?php

    namespace App\Service;
    use Exception;
    use GeoIp2\Database\Reader;
    use function sha1;

    /**
     * Class IpDetection
     * @package App\Service
     */
    class IpDetection
    {
        /**
         * @var Reader
         */
        private $maxMindReader;

        /**
         * @var array
         */
        private $cache;

        /**
         * IpCountry constructor.
         *
         * @param $databasePath
         *
         * @throws \MaxMind\Db\Reader\InvalidDatabaseException
         */
        public function __construct($databasePath)
        {
            $this->maxMindReader = new Reader($databasePath);
        }

        /**
         * Resolves IP to a Country
         *
         * @param string $ip
         *
         * @return array
         */
        public function getCountry($ip)
        {
            // Simple caching mechanism to speed up ip translation to a country
            $cacheKey = sha1($ip);
            if (isset($this->cache[$cacheKey]))
                return $this->cache[$cacheKey];

            $returnData = [
                'country' =>
                    [
                        'shortCode' => 'US',
                        'name' => 'United States'
                    ],
                'ip' => $ip
            ];

            // Finding out from which country is user IP address
            try
            {
                $readerData = $this->maxMindReader->country($ip);
                $country = $readerData->country->isoCode;
                $country = $country == 'GB' ? 'UK' : $country;
                $returnData['country']['shortCode'] = $country;
                $returnData['country']['name'] = $readerData->country->name;

                $this->cache[$cacheKey] = $returnData;
            }
            catch (Exception $e)
            {
                // Nothing to do here
            }

            return $returnData;
        }

        /**
         * Resolves IP to a City
         *
         * @param string $ip
         *
         * @return array
         */
        public function getCity($ip)
        {
            // Simple caching mechanism to speed up ip translation to a country
            $cacheKey = sha1($ip);
            if (isset($this->cache[$cacheKey]))
                return $this->cache[$cacheKey];

            $returnData = [
                'country' =>
                    [
                        'shortCode' => 'US',
                        'name' => 'United States'
                    ],
                'city' => '',
                'ip' => $ip
            ];

            // Finding out from which city/country is user IP address
            try
            {
                $readerData = $this->maxMindReader->city($ip);
                $country = $readerData->country->isoCode;
                $country = $country == 'GB' ? 'UK' : $country;
                $returnData['country']['shortCode'] = $country;
                $returnData['country']['name'] = $readerData->country->name;

                $returnData['city'] = $readerData->city->name;

                $this->cache[$cacheKey] = $returnData;
            }
            catch (Exception $e)
            {
                // Nothing to do here
            }

            return $returnData;
        }
    }

    // END
