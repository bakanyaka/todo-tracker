<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static No()
 * @method static static Yes()
 * @method static static Soon()
 */
final class OverdueState extends Enum
{
    public const No = 'no';
    public const Yes = 'yes';
    public const Soon = 'soon';
}
