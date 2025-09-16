<?php

namespace Eclipse\Catalogue\Enums\StructuredData;

enum ItemAvailability: string
{
    case DISCONTINUED = 'Discontinued';
    case IN_STOCK = 'InStock';
    case IN_STORE_ONLY = 'InStoreOnly';
    case LIMITED_AVAILABILITY = 'LimitedAvailability';
    case ONLINE_ONLY = 'OnlineOnly';
    case OUT_OF_STOCK = 'OutOfStock';
    case PREORDER = 'PreOrder';
    case PRESALE = 'PreSale';
    case SOLD_OUT = 'SoldOut';

    /**
     * Human readable values suitable for display.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::DISCONTINUED->value => 'Discontinued - Indicates that the item has been discontinued.',
            self::IN_STOCK->value => 'InStock - Indicates that the item is in stock.',
            self::IN_STORE_ONLY->value => 'InStoreOnly - Indicates that the item is available only at physical locations.',
            self::LIMITED_AVAILABILITY->value => 'LimitedAvailability - Indicates that the item has limited availability.',
            self::ONLINE_ONLY->value => 'OnlineOnly - Indicates that the item is available only online.',
            self::OUT_OF_STOCK->value => 'OutOfStock - Indicates that the item is out of stock.',
            self::PREORDER->value => 'PreOrder - Indicates that the item is available for pre-order, but will be delivered when generally available.',
            self::PRESALE->value => 'PreSale - Indicates that the item is available for ordering and delivery before general availability.',
            self::SOLD_OUT->value => 'SoldOut - Indicates that the item has sold out.',
        ];
    }
}
