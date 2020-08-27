<?php

namespace Laravel\VaporCli;

class CacheInstanceClasses
{
    /**
     * Get the available general purpose cache instance classes.
     *
     * @return array
     */
    public static function general()
    {
        return [
            'cache.t3.micro'    => 'cache.t3.micro - (2 VCPU, 0.5Gib RAM) - Free Tier Eligible ~$13 / month',
            'cache.t3.small'    => 'cache.t3.small - (2 VCPU, 1.37Gib RAM) - ~$25 / month',
            'cache.t3.medium'   => 'cache.t3.medium - (2 VCPU, 3.09Gib RAM) - ~$50 / month',
            'cache.m5.large'    => 'cache.m5.large - (2 VCPU, 6.38Gib RAM) - ~$112 / month',
            'cache.m5.xlarge'   => 'cache.m5.xlarge - (4 VCPU, 12.93Gib RAM) - ~$224 / month',
            'cache.m5.2xlarge'  => 'cache.m5.2xlarge - (8 VCPU, 26.04Gib RAM) - ~$449 / month',
            'cache.m5.4xlarge'  => 'cache.m5.4xlarge - (16 VCPU, 52.26Gib RAM) - ~$897 / month',
            'cache.m5.12xlarge' => 'cache.m5.12xlarge - (48 VCPU, 157.12Gib RAM) - ~$2696 / month',
            'cache.m5.24xlarge' => 'cache.m5.24xlarge - (96 VCPU, 314.32Gib RAM) - ~$5392 / month',
        ];
    }

    /**
     * Get the available memory optimized cache instance classes.
     *
     * @return array
     */
    public static function memory()
    {
        return [
            'cache.r5.large'    => 'cache.r5.large - (2 VCPU, 13.07Gib RAM) - ~$156 / month',
            'cache.r5.xlarge'   => 'cache.r5.xlarge - (4 VCPU, 26.32GiB RAM) - ~$310 / month',
            'cache.r5.2xlarge'  => 'cache.r5.2xlarge - (8 VCPU, 52.82GiB RAM) - ~$621 / month',
            'cache.r5.4xlarge'  => 'cache.r5.4xlarge - (16 VCPU, 105.81GiB RAM) - ~$1241 / month',
            'cache.r5.12xlarge' => 'cache.r5.12xlarge - (48 VCPU, 317.77GiB RAM) - ~$3732 / month',
            'cache.r5.24xlarge' => 'cache.r5.24xlarge - (96 VCPU, 635.61GiB RAM) - ~$7465 / month',
        ];
    }
}
