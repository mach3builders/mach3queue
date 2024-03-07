<?php

namespace Mach3queue\Dashboard;

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

    private function replaceTagWithScript(mixed $script_tag): void
    {
        $src = $script_tag->getAttribute('src');
        $script_content = file_get_contents(DashboardHtml::PATH . $src);

        $script_tag->removeAttribute('src');
        $script_tag->textContent = $script_content;
    }
}