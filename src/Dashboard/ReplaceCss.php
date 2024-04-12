<?php

namespace Mach3queue\Dashboard;

use DOMElement;
use DOMDocument;
use DOMNodeList;
use DOMException;

class ReplaceCss
{
    private DOMDocument $html;

    /**
     * @throws DOMException
     */
    public function replace(DOMDocument $html): DOMDocument
    {
        $this->html = $html;

        foreach ($this->linkTags() as $link_tag) {
            if (!$this->linkIsStylesheet($link_tag)) {
                continue;
            }

            $this->replaceTagWithStyle($link_tag);
        }

        return $this->html;
    }

    private function linkTags(): DOMNodeList
    {
        return $this->html->getElementsByTagName('link');
    }

    private function linkIsStylesheet(DOMElement $link_tag): bool
    {
        return $link_tag->getAttribute('rel') == 'stylesheet'
            && $link_tag->hasAttribute('href');
    }

    /**
     * @throws DOMException
     */
    private function replaceTagWithStyle(DOMElement $link_tag): void
    {
        $style = $this->createStyleTag($link_tag);

        $link_tag->parentNode->replaceChild($style, $link_tag);
    }

    /**
     * @throws DOMException
     */
    private function createStyleTag(DOMElement $link_tag): DOMElement|false
    {
        $href = $link_tag->getAttribute('href');
        $content = file_get_contents(DashboardHtml::$path.$href);

        return $this->html->createElement('style', $content);
    }
}