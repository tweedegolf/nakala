<?php

namespace Tweedegolf\Nakala\Event;

final class DocumentEvents
{
    const BEFORE_ROOT_ELEMENT = 'nakala.before_root_element';
    const AFTER_ROOT_ELEMENT = 'nakala.after_root_element';

    const LIST_ITEM_CREATED = 'nakala.list_item_created';
    const PARAGRAPH_CREATED = 'nakala.paragraph_created';
    const IMAGE_CREATED = 'nakala.image_created';
    const TITLE_CREATED = 'nakala.title_created';
    const LINK_CREATED = 'nakala.link_created';

    const TABLE_CREATED = 'nakala.table_created';
    const TABLE_ROW_CREATED = 'nakala.table_row_created';
    const TABLE_CELL_CREATED = 'nakala.table_cell_created';
}
