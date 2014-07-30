<?php

namespace Tweedegolf\Nakala;

final class ElementAttributes
{
    /**
     * List and header depth
     */
    const DEPTH = 'depth';

    const BOLD = 'bold';
    const ITALIC = 'italic';
    const UNDERLINE = 'underline';
    const STRIKETHROUGH = 'strikethrough';

    const SUPERSCRIPT = 'superScript';
    const SUBSCRIPT = 'subScript';

    /**
     * Url to target for links
     */
    const LINK = 'link';

    /**
     * Whether or not list items are numbered
     */
    const NUMBERED = 'numbered';

    /**
     * Whether or not a table cell is a header cell
     */
    const HEADER = 'header';
}
