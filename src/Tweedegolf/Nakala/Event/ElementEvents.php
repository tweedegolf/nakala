<?php

namespace Tweedegolf\Nakala\Event;

final class ElementEvents
{
    const PARAGRAPH_START = 'paragraph_start';
    const PARAGRAPH_END = 'paragraph_end';

    const LIST_START = 'list_start';
    const LIST_END = 'list_end';

    const LIST_ITEM_START = 'list_item_start';
    const LIST_ITEM_END = 'list_item_end';

    const TABLE_START = 'table_start';
    const TABLE_END = 'table_end';

    const TABLE_ROW_START = 'table_row_start';
    const TABLE_ROW_END = 'table_row_end';

    const TABLE_CELL_START = 'table_cell_start';
    const TABLE_CELL_END = 'table_cell_end';

    const TITLE = 'title';

    const LINK = 'link';
    const TEXT = 'text';
    const IMAGE = 'image';
} 
