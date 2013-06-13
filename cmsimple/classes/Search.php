<?php

/**
 * The search function of CMSimple_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id: search.php 607 2013-06-13 15:54:56Z cmb69 $
 * @link      http://cmsimple-xh.org/
 */


/*
  ======================================
  $CMSIMPLE_XH_VERSION$
  $CMSIMPLE_XH_DATE$
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.com
  ======================================
  -- COPYRIGHT INFORMATION START --
  Based on CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  (c) 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  This file is part of CMSimple_XH
  For licence see notice in /cmsimple/cms.php
  -- COPYRIGHT INFORMATION END --
  ======================================
 */


/**
 * The search class.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_Search
{
    /**
     * The search String.
     *
     * @var string
     *
     * @access protected
     */
    var $searchString;

    /**
     * The search words.
     *
     * @var array
     *
     * @access protected
     */
    var $words;

    /**
     * Constructs an instance.
     *
     * @param string $searchString String The search string.
     */
    function XH_Search($searchString)
    {
        $this->searchString = $searchString;
    }

    /**
     * Returns the array of search words.
     *
     * @return array
     *
     * @access protected
     */
    function getWords()
    {
        if (!isset($this->words)) {
            $search = utf8_strtolower($this->searchString);
            $words = explode(' ', $search);
            $this->words = array();
            foreach ($words as $word) {
                $word = trim($word);
                if ($word != '') {
                    $this->words[] = $word;
                }
            }
        }
        return $this->words;
    }

    /**
     * Returns an array of page indexes
     * where all words of the search string are contained.
     *
     * @return array
     *
     * @global array The content of the pages.
     * @global array The configuration of the core.
     */
    function search()
    {
        global $c, $cf;

        $result = array();
        $words = $this->getWords();
        if (empty($words)) {
            return $result;
        }
        foreach ($c as $i => $content) {
            if (!hide($i) || $cf['show_hidden']['pages_search'] == 'true') {
                $found  = true;
                $content = evaluate_plugincall($content);
                $content = utf8_strtolower(strip_tags($content));
                // html_entity_decode() doesn't work reliably under PHP 4 for UTF-8
                $decode = array(
                    '&amp;' => '&',
                    '&quot;' => '"',
                    '&apos;' => '\'',
                    '&lt;' => '<',
                    '&gt;' => '>'
                );
                $content = strtr($content, $decode);
                foreach ($words as $word) {
                    if (strpos($content, $word) === false) {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    $result[] = $i;
                }
            }
        }
        return $result;
    }

    /**
     * Returns the search results view.
     *
     * @return string (X)HTML
     *
     * @global array  The headings of the pages.
     * @global array  The URLs of the pages.
     * @global string The script name.
     * @global array  The localization of the core.
     */
    function render()
    {
        global $h, $u, $sn, $tx;

        $o .= '<h1>' . $tx['search']['result'] . '</h1><p>"'
            . htmlspecialchars($this->searchString, ENT_QUOTES, 'UTF-8') . '" ';
        $words = $this->getWords();
        $pages = $this->search();
        $count = count($pages);
        if ($count == 0) {
            $o .= $tx['search']['notfound'] . '.</p>';
        } else {
            $o .= $tx['search']['foundin'] . ' ' . $count . ' ';
            if ($count > 1) {
                $o .= $tx['search']['pgplural'];
            } else {
                $o .= $tx['search']['pgsingular'];
            }
            $o .= ':</p>';
            $o .= "\n" .'<ul>';
            $words = implode(' ', $words);
            foreach ($pages as $i) {
                $o .= "\n\t"
                    . '<li><a href="' . $sn . '?' . $u[$i] . '&amp;search='
                    . urlencode($words) .'">' . $h[$i] . '</a></li>';
            }
            $o .= "\n" . '</ul>' . "\n";
        }
        return $o;
    }
}

?>