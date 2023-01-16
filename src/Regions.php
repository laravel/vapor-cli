<?php

namespace Laravel\VaporCli;

class Regions
{
    /**
     * Get the available regions.
     *
     * @return array
     */
    public static function available()
    {
        return [
            'us-east-1'  => 'US East (N. Virginia) (us-east-1)',
            'us-east-2'  => 'US East (Ohio) (us-east-2)',
            'us-west-1'  => 'US West (N. California) (us-west-1)',
            'us-west-2'  => 'US West (Oregon) (us-west-2)',
            'af-south-1' => 'Africa (Capetown) (af-south-1)',
            'ap-east-1'  => 'Asia Pacific (Hong Kong) (ap-east-1)',
            'ap-south-1' => 'Asia Pacific (Mumbai) (ap-south-1)',
            'ap-northeast-3' => 'Asia Pacific (Osaka) (ap-northeast-3)',
            'ap-northeast-2' => 'Asia Pacific (Seoul) (ap-northeast-2)',
            'ap-southeast-1' => 'Asia Pacific (Singapore) (ap-southeast-1)',
            'ap-southeast-2' => 'Asia Pacific (Sydney) (ap-southeast-2)',
            'ap-southeast-3' => 'Asia Pacific (Jakarta) (ap-southeast-3)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo) (ap-northeast-1)',
            'ca-central-1'   => 'Canada (Central) (ca-central-1)',
            // 'cn-north-1' => 'China (Beijing) (cn-north-1)',
            // 'cn-northwest-1' => 'China (Ningxia) (cn-northwest-1)',
            'eu-central-1' => 'Europe (Frankfurt) (eu-central-1)',
            'eu-west-1'    => 'Europe (Ireland) (eu-west-1)',
            'eu-west-2'    => 'Europe (London) (eu-west-2)',
            'eu-west-3'    => 'Europe (Paris) (eu-west-3)',
            'eu-north-1'   => 'Europe (Stockholm) (eu-north-1)',
            'eu-south-1'   => 'Europe (Milan) (eu-south-1)',
            'me-south-1'   => 'Middle East (Bahrain) (me-south-1)',
            'sa-east-1'    => 'South America (São Paulo) (sa-east-1)',
        ];
    }
}
