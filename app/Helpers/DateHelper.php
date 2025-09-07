<?php

if (!function_exists('safe_date_format')) {
    /**
     * Safely format a date value that might be string or Carbon instance
     *
     * @param mixed $date
     * @param string $format
     * @param string $default
     * @return string
     */
    function safe_date_format($date, $format = 'd/m/Y H:i:s', $default = 'ไม่ได้ระบุ')
    {
        if (empty($date)) {
            return $default;
        }

        try {
            // If it's already a Carbon instance
            if ($date instanceof \Carbon\Carbon) {
                return $date->format($format);
            }

            // If it's a DateTime instance
            if ($date instanceof \DateTime) {
                return $date->format($format);
            }

            // If it's a string, try to parse it
            if (is_string($date)) {
                $carbonDate = \Carbon\Carbon::parse($date);
                return $carbonDate->format($format);
            }

            // If it's a timestamp
            if (is_numeric($date)) {
                $carbonDate = \Carbon\Carbon::createFromTimestamp($date);
                return $carbonDate->format($format);
            }

            return $default;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::warning('Date formatting error', [
                'date' => $date,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            return $default;
        }
    }
}

if (!function_exists('safe_date_diff')) {
    /**
     * Safely get human readable difference for dates
     *
     * @param mixed $date
     * @param string $default
     * @return string
     */
    function safe_date_diff($date, $default = 'ไม่ทราบ')
    {
        if (empty($date)) {
            return $default;
        }

        try {
            // If it's already a Carbon instance
            if ($date instanceof \Carbon\Carbon) {
                return $date->diffForHumans();
            }

            // If it's a DateTime instance
            if ($date instanceof \DateTime) {
                return \Carbon\Carbon::instance($date)->diffForHumans();
            }

            // If it's a string, try to parse it
            if (is_string($date)) {
                $carbonDate = \Carbon\Carbon::parse($date);
                return $carbonDate->diffForHumans();
            }

            // If it's a timestamp
            if (is_numeric($date)) {
                $carbonDate = \Carbon\Carbon::createFromTimestamp($date);
                return $carbonDate->diffForHumans();
            }

            return $default;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Date diff error', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            
            return $default;
        }
    }
}
