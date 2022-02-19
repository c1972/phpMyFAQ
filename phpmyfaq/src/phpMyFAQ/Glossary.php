<?php

/**
 * The main glossary class.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2005-2022 phpMyFAQ Team
 * @license   https://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2005-09-15
 */

namespace phpMyFAQ;

/**
 * Class Glossary
 *
 * @package phpMyFAQ
 */
class Glossary
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * Item.
     *
     * @var array
     */
    private $item = [];

    /**
     * Definition of an item.
     *
     * @var string
     */
    private $definition = '';

    /**
     * Constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Fill the passed string with the current Glossary items.
     *
     * @param string $content Content
     *
     * @return string
     */
    public function insertItemsIntoContent($content = '')
    {
        if ('' == $content) {
            return '';
        }

        $attributes = [
            'href',
            'src',
            'title',
            'alt',
            'class',
            'style',
            'id',
            'name',
            'face',
            'size',
            'dir',
            'rel',
            'rev',
            'onmouseenter',
            'onmouseleave',
            'onafterprint',
            'onbeforeprint',
            'onbeforeunload',
            'onhashchange',
            'onmessage',
            'onoffline',
            'ononline',
            'onpopstate',
            'onpagehide',
            'onpageshow',
            'onresize',
            'onunload',
            'ondevicemotion',
            'ondeviceorientation',
            'onabort',
            'onblur',
            'oncanplay',
            'oncanplaythrough',
            'onchange',
            'onclick',
            'oncontextmenu',
            'ondblclick',
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'ondurationchange',
            'onemptied',
            'onended',
            'onerror',
            'onfocus',
            'oninput',
            'oninvalid',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onload',
            'onloadeddata',
            'onloadedmetadata',
            'onloadstart',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onpause',
            'onplay',
            'onplaying',
            'onprogress',
            'onratechange',
            'onreset',
            'onscroll',
            'onseeked',
            'onseeking',
            'onselect',
            'onshow',
            'onstalled',
            'onsubmit',
            'onsuspend',
            'ontimeupdate',
            'onvolumechange',
            'onwaiting',
            'oncopy',
            'oncut',
            'onpaste',
            'onbeforescriptexecute',
            'onafterscriptexecute',
        ];

        foreach ($this->getAllGlossaryItems() as $item) {
            $this->definition = $item['definition'];
            $item['item'] = preg_quote($item['item'], '/');
            $content = Strings::preg_replace_callback(
                '/'
                // a. the glossary item could be an attribute name
                . '(' . $item['item'] . '="[^"]*")|'
                // b. the glossary item could be inside an attribute value
                . '((' . implode('|', $attributes) . ')="[^"]*' . $item['item'] . '[^"]*")|'
                // c. the glossary item could be everywhere as a distinct word
                . '(\W+)(' . $item['item'] . ')(\W+)|'
                // d. the glossary item could be at the beginning of the string as a distinct word
                . '^(' . $item['item'] . ')(\W+)|'
                // e. the glossary item could be at the end of the string as a distinct word
                . '(\W+)(' . $item['item'] . ')$'
                . '/mis',
                [$this, 'setTooltip'],
                $content,
                1
            );
        }

        return $content;
    }

    /**
     * Gets all items and definitions from the database.
     *
     * @return array
     */
    public function getAllGlossaryItems()
    {
        $items = [];

        $query = sprintf(
            "
            SELECT
                id, item, definition
            FROM
                %sfaqglossary
            WHERE
                lang = '%s'
            ORDER BY item ASC",
            Database::getTablePrefix(),
            $this->config->getLanguage()->getLanguage()
        );

        $result = $this->config->getDb()->query($query);

        while ($row = $this->config->getDb()->fetchObject($result)) {
            $items[] = [
                'id' => $row->id,
                'item' => stripslashes($row->item),
                'definition' => stripslashes($row->definition),
            ];
        }

        return $items;
    }

    /**
     * Callback function for filtering HTML from URLs and images.
     *
     * @param array $matches Matches
     *
     * @return string
     */
    public function setTooltip(array $matches)
    {
        $prefix = $postfix = '';

        if (count($matches) > 9) {
            // if the word is at the end of the string
            $prefix = $matches[9];
            $item = $matches[10];
            $postfix = '';
        } elseif (count($matches) > 7) {
            // if the word is at the beginning of the string
            $prefix = '';
            $item = $matches[7];
            $postfix = $matches[8];
        } elseif (count($matches) > 4) {
            // if the word is else where in the string
            $prefix = $matches[4];
            $item = $matches[5];
            $postfix = $matches[6];
        }

        if (!empty($item)) {
            return sprintf(
                '%s<abbr data-toggle="tooltip" data-placement="bottom" title="%s">%s</abbr>%s',
                $prefix,
                $this->definition,
                $item,
                $postfix
            );
        }

        // Fallback: the original matched string
        return $matches[0];
    }

    /**
     * Gets one item and definition from the database.
     *
     * @param int $id Glossary ID
     *
     * @return array
     */
    public function getGlossaryItem($id)
    {
        $item = [];

        $query = sprintf(
            "
            SELECT
                id, item, definition
            FROM
                %sfaqglossary
            WHERE
                id = %d AND lang = '%s'",
            Database::getTablePrefix(),
            (int)$id,
            $this->config->getLanguage()->getLanguage()
        );

        $result = $this->config->getDb()->query($query);

        while ($row = $this->config->getDb()->fetchObject($result)) {
            $item = [
                'id' => $row->id,
                'item' => stripslashes($row->item),
                'definition' => stripslashes($row->definition),
            ];
        }

        return $item;
    }

    /**
     * Inserts an item and definition into the database.
     *
     * @param string $item       Item
     * @param string $definition Definition
     *
     * @return bool
     */
    public function addGlossaryItem($item, $definition)
    {
        $this->item = $this->config->getDb()->escape($item);
        $this->definition = $this->config->getDb()->escape($definition);

        $query = sprintf(
            "
            INSERT INTO
                %sfaqglossary
            (id, lang, item, definition)
                VALUES
            (%d, '%s', '%s', '%s')",
            Database::getTablePrefix(),
            $this->config->getDb()->nextId(Database::getTablePrefix() . 'faqglossary', 'id'),
            $this->config->getLanguage()->getLanguage(),
            Strings::htmlspecialchars($this->item),
            Strings::htmlspecialchars($this->definition)
        );

        if ($this->config->getDb()->query($query)) {
            return true;
        }

        return false;
    }

    /**
     * Updates an item and definition into the database.
     *
     * @param int    $id         Glossary ID
     * @param string $item       Item
     * @param string $definition Definition
     *
     * @return bool
     */
    public function updateGlossaryItem($id, $item, $definition)
    {
        $this->item = $this->config->getDb()->escape($item);
        $this->definition = $this->config->getDb()->escape($definition);

        $query = sprintf(
            "
            UPDATE
                %sfaqglossary
            SET
                item = '%s',
                definition = '%s'
            WHERE
                id = %d AND lang = '%s'",
            Database::getTablePrefix(),
            Strings::htmlspecialchars($this->item),
            Strings::htmlspecialchars($this->definition),
            (int)$id,
            $this->config->getLanguage()->getLanguage()
        );

        if ($this->config->getDb()->query($query)) {
            return true;
        }

        return false;
    }

    /**
     * Deletes an item and definition into the database.
     *
     * @param int $id Glossary ID
     *
     * @return bool
     */
    public function deleteGlossaryItem($id)
    {
        $query = sprintf(
            "
            DELETE FROM
                %sfaqglossary
            WHERE
                id = %d AND lang = '%s'",
            Database::getTablePrefix(),
            (int)$id,
            $this->config->getLanguage()->getLanguage()
        );

        if ($this->config->getDb()->query($query)) {
            return true;
        }

        return false;
    }
}
