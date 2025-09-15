<?php

namespace Eclipse\Catalogue\Support;

class ItemAvailability
{
    public const DISCONTINUED = 'Discontinued';

    public const IN_STOCK = 'InStock';

    public const IN_STORE_ONLY = 'InStoreOnly';

    public const LIMITED_AVAILABILITY = 'LimitedAvailability';

    public const ONLINE_ONLY = 'OnlineOnly';

    public const OUT_OF_STOCK = 'OutOfStock';

    public const PREORDER = 'PreOrder';

    public const PRESALE = 'PreSale';

    public const SOLD_OUT = 'SoldOut';

    /**
     * Human readable values suitable for display.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::DISCONTINUED => 'Discontinued - Indicates that the item has been discontinued.',
            self::IN_STOCK => 'InStock - Indicates that the item is in stock.',
            self::IN_STORE_ONLY => 'InStoreOnly - Indicates that the item is available only at physical locations.',
            self::LIMITED_AVAILABILITY => 'LimitedAvailability - Indicates that the item has limited availability.',
            self::ONLINE_ONLY => 'OnlineOnly - Indicates that the item is available only online.',
            self::OUT_OF_STOCK => 'OutOfStock - Indicates that the item is out of stock.',
            self::PREORDER => 'PreOrder - Indicates that the item is available for pre-order, but will be delivered when generally available.',
            self::PRESALE => 'PreSale - Indicates that the item is available for ordering and delivery before general availability.',
            self::SOLD_OUT => 'SoldOut - Indicates that the item has sold out.',
        ];
    }
}
