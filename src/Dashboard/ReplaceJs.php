<?php

namespace Mach3queue\Dashboard;

use DOMElement;
use DOMDocument;
use DOMNodeList;

class ReplaceJs
{
    private DOMDocument $html;

    public function replace(DOMDocument $html): DOMDocument
    {
        $this->html = $html;

        foreach ($this->scriptTags() as $script_tag) {
            $this->replaceTagWithScript($script_tag);
        }

        return $this->html;
    }

    private function scriptTags(): DOMNodeList
    {
        return $this->html->getElementsByTagName('script');
    }

    private function replaceTagWithScript(DOMElement $script_tag): void
    {
        $src = $script_tag->getAttribute('src');
        $script_content = file_get_contents(DashboardHtml::$path . $src);

        $script_tag->removeAttribute('src');
        $script_tag->textContent = $script_content;
    }
}