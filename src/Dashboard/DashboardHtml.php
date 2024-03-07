<?php

namespace Mach3queue\Dashboard;

use DOMDocument;
use DOMException;

class DashboardHtml
{
    const string PATH = __DIR__ . '/../../dist/';
    private DOMDocument $html;

    /**
     * @throws DOMException
     */
    public function parse(): string
    {
        $this->initiateHtmlAsDom();
        $this->replaceCss();
        $this->replaceJs();

        return $this->html->saveHTML();
    }

    private function initiateHtmlAsDom(): void
    {
        $this->html = new DOMDocument();
        $this->html->loadHTML(file_get_contents(self::PATH . 'index.html'));
    }

    /**
     * @throws DOMException
     */
    private function replaceCss(): void
    {
        $this->html = (new ReplaceCss())->replace($this->html);
    }

    private function replaceJs(): void
    {
        $this->html = (new ReplaceJs())->replace($this->html);
    }
}