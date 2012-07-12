<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Feed
 */

namespace Zend\Feed\Writer\Extension\Threading\Renderer;

use DOMDocument;
use DOMElement;
use Zend\Feed\Writer\Extension;

/**
* @category Zend
* @package Zend_Feed_Writer
*/
class Entry extends Extension\AbstractRenderer
{

    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $_called = false;

    /**
     * Render entry
     *
     * @return void
     */
    public function render()
    {
        if (strtolower($this->getType()) == 'rss') {
            return; // Atom 1.0 only
        }
        $this->_setCommentLink($this->_dom, $this->_base);
        $this->_setCommentFeedLinks($this->_dom, $this->_base);
        $this->_setCommentCount($this->_dom, $this->_base);
        if ($this->_called) {
            $this->_appendNamespaces();
        }
    }

    /**
     * Append entry namespaces
     *
     * @return void
     */
    protected function _appendNamespaces()
    {
        $this->getRootElement()->setAttribute('xmlns:thr',
            'http://purl.org/syndication/thread/1.0');
    }

    /**
     * Set comment link
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCommentLink(DOMDocument $dom, DOMElement $root)
    {
        $link = $this->getDataContainer()->getCommentLink();
        if (!$link) {
            return;
        }
        $clink = $this->_dom->createElement('link');
        $clink->setAttribute('rel', 'replies');
        $clink->setAttribute('type', 'text/html');
        $clink->setAttribute('href', $link);
        $count = $this->getDataContainer()->getCommentCount();
        if ($count !== null) {
            $clink->setAttribute('thr:count', $count);
        }
        $root->appendChild($clink);
        $this->_called = true;
    }

    /**
     * Set comment feed links
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCommentFeedLinks(DOMDocument $dom, DOMElement $root)
    {
        $links = $this->getDataContainer()->getCommentFeedLinks();
        if (!$links || empty($links)) {
            return;
        }
        foreach ($links as $link) {
            $flink = $this->_dom->createElement('link');
            $flink->setAttribute('rel', 'replies');
            $flink->setAttribute('type', 'application/'. $link['type'] .'+xml');
            $flink->setAttribute('href', $link['uri']);
            $count = $this->getDataContainer()->getCommentCount();
            if ($count !== null) {
                $flink->setAttribute('thr:count', $count);
            }
            $root->appendChild($flink);
            $this->_called = true;
        }
    }

    /**
     * Set entry comment count
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    protected function _setCommentCount(DOMDocument $dom, DOMElement $root)
    {
        $count = $this->getDataContainer()->getCommentCount();
        if ($count === null) {
            return;
        }
        $tcount = $this->_dom->createElement('thr:total');
        $tcount->nodeValue = $count;
        $root->appendChild($tcount);
        $this->_called = true;
    }
}
