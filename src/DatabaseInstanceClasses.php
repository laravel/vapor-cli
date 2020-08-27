<?php

namespace Laravel\VaporCli;

class DatabaseInstanceClasses
{
    /**
     * Get the available RDS instance classes.
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(static::available());
    }

    /**
     * Get the available general purpose RDS instance classes.
     *
     * @return array
     */
    public static function general()
    {
        return [
            // 'db.t2.micro' => 'db.t2.micro - (1 VCPU, 1Gib RAM) - Free Tier Eligible / ~$15 / month',
            // 'db.t2.small' => 'db.t2.small - (1 VCPU, 2Gib RAM) - ~$25 / month',
            // 'db.t2.medium' => 'db.t2.medium - (2 VCPU, 4Gib RAM) - ~$50 / month',
            // 'db.t2.large' => 'db.t2.large - (2 VCPU, 8Gib RAM) - ~$100 / month',

            'db.t3.micro'  => 'db.t3.micro - (1 VCPU, 1Gib RAM) - ~$15 / month',
            'db.t3.small'  => 'db.t3.small - (1 VCPU, 2Gib RAM) - ~$25 / month',
            'db.t3.medium' => 'db.t3.medium - (2 VCPU, 4Gib RAM) - ~$50 / month',
            'db.t3.large'  => 'db.t3.large - (2 VCPU, 8Gib RAM) - ~$100 / month',

            'db.m5.large'    => 'db.m5.large - (2 VCPU, 8GB RAM) - ~$125 / month',
            'db.m5.xlarge'   => 'db.m5.xlarge - (4 VCPU, 16Gib RAM) - ~$250 / month',
            'db.m5.2xlarge'  => 'db.m5.2xlarge - (8 VCPU, 32Gib RAM) - ~$500 / month',
            'db.m5.4xlarge'  => 'db.m5.4xlarge - (16 VCPU, 64Gib RAM) - ~$1100 / month',
            'db.m5.12xlarge' => 'db.m5.12xlarge - (48 VCPU, 192GiB RAM) - ~$3300 / month',
            'db.m5.24xlarge' => 'db.m5.24xlarge - (96 VCPU, 384GiB RAM) - ~$6600 / month',
        ];
    }

    /**
     * Get the available memory optimized RDS instance classes.
     *
     * @return array
     */
    public static function memory()
    {
        return [
            'db.r5.large'    => 'db.r5.large - (2 VCPU, 16Gib RAM) - ~$173 / month',
            'db.r5.xlarge'   => 'db.r5.xlarge - (4 VCPU, 32Gib RAM) - ~$346 / month',
            'db.r5.2xlarge'  => 'db.r5.2xlarge - (8 VCPU, 64Gib RAM) - ~$691 / month',
            'db.r5.4xlarge'  => 'db.r5.4xlarge - (16 VCPU, 128Gib RAM) - ~$1382 / month',
            'db.r5.12xlarge' => 'db.r5.12xlarge - (48 VCPU, 384Gib RAM) - ~$4147 / month',
            'db.r5.24xlarge' => 'db.r5.24xlarge - (96 VCPU, 768Gib RAM) - ~$8294 / month',
        ];
    }
}
