<?php

namespace FpmCheck;

class Helper 
{
    public static function checkFpmStatus($response)
    {
        $status = json_decode($response, true);
        if ($status['listen queue'] > 0
            && $status['active processes'] >= $status['total processes']
        ) {
            throw new \Exception(sprintf('FPM is at capacity [queue=%d],[processes=%d/%d]',
                    $status['active processes'] >= $status['total processes'])
            );
        }
    }
}
