<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class NovupayWhitePaperDocx
{
    public function downloadResponse()
    {
        $path = tempnam(sys_get_temp_dir(), 'novupay-wp-');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary white paper file.');
        }

        $docxPath = $path.'.docx';
        rename($path, $docxPath);
        $this->write($docxPath);

        return response()->download(
            $docxPath,
            'NovuPay-OPS-White-Paper.docx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    public function write(string $path): void
    {
        $blocks = $this->blocksFromHtml(view('pages.novupay-whitepaper')->render());
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create the white paper document.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('docProps/core.xml', $this->coreProps());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRels());
        $zip->addFromString('word/styles.xml', $this->styles());
        $zip->addFromString('word/document.xml', $this->documentXml($blocks));
        $zip->close();
    }

    private function blocksFromHtml(string $html): array
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $blocks = [];

        $this->push($blocks, 'kicker', $this->firstText($xpath, $this->classQuery('section-kicker')));
        $this->push($blocks, 'title', $this->firstText($xpath, $this->classQuery('section-title')));
        $this->push($blocks, 'subtitle', $this->firstText($xpath, $this->classQuery('section-subtitle')));
        $this->push($blocks, 'h1', $this->firstText($xpath, "//h1[contains(concat(' ', normalize-space(@class), ' '), ' whitepaper-hero-title ')]"));

        $hero = $xpath->query("//h1[contains(concat(' ', normalize-space(@class), ' '), ' whitepaper-hero-title ')]/following-sibling::p[1]")->item(0);
        if ($hero instanceof DOMNode) {
            $this->push($blocks, 'p', $this->text($hero));
        }

        foreach ($xpath->query($this->classQuery('whitepaper-hero-stats').'/div') as $stat) {
            if (! $stat instanceof DOMElement) {
                continue;
            }
            $parts = [];
            foreach ($stat->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $part = $this->text($child);
                    if ($part !== '') {
                        $parts[] = $part;
                    }
                }
            }
            if (count($parts) >= 2) {
                $this->push($blocks, 'p', $parts[0].': '.$parts[1]);
            }
        }

        $content = $xpath->query($this->classQuery('doc-content'))->item(0);
        if ($content instanceof DOMNode) {
            $this->walk($content, $blocks);
        }

        return $blocks;
    }

    private function walk(DOMNode $node, array &$blocks): void
    {
        if (! $node instanceof DOMElement) {
            return;
        }

        $class = ' '.$node->getAttribute('class').' ';
        if (str_contains($class, ' whitepaper-actions ')) {
            return;
        }

        $tag = strtolower($node->tagName);
        if ($tag === 'h2') {
            $this->push($blocks, 'h2', $this->text($node));

            return;
        }
        if ($tag === 'h5') {
            $this->push($blocks, 'h3', $this->text($node));

            return;
        }
        if (str_contains($class, ' doc-section-kicker ')) {
            $this->push($blocks, 'kicker', $this->text($node));

            return;
        }
        if ($tag === 'p') {
            $this->push($blocks, 'p', $this->text($node));

            return;
        }
        if ($tag === 'li') {
            $this->push($blocks, 'li', $this->text($node));

            return;
        }

        foreach ($node->childNodes as $child) {
            $this->walk($child, $blocks);
        }
    }

    private function push(array &$blocks, string $type, string $text): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }
        $blocks[] = ['type' => $type, 'text' => $text];
    }

    private function firstText(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)->item(0);

        return $node instanceof DOMNode ? $this->text($node) : '';
    }

    private function classQuery(string $class): string
    {
        return "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
    }

    private function text(DOMNode $node): string
    {
        $text = preg_replace('/\s+/u', ' ', $node->textContent ?? '') ?? '';

        return trim($text);
    }

    private function documentXml(array $blocks): string
    {
        $body = '';
        foreach ($blocks as $block) {
            $style = match ($block['type']) {
                'title' => 'Title',
                'subtitle' => 'Subtitle',
                'h1', 'h2' => 'Heading1',
                'h3' => 'Heading2',
                'kicker' => 'Kicker',
                'li' => 'ListBullet',
                default => 'BodyText',
            };
            $text = $block['type'] === 'li' ? '• '.$block['text'] : $block['text'];
            $body .= $this->paragraph($style, $text);
        }

        $body .= '<w:sectPr>'
            .'<w:pgSz w:w="12240" w:h="15840"/>'
            .'<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>'
            .'</w:sectPr>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$body.'</w:body></w:document>';
    }

    private function paragraph(string $style, string $text): string
    {
        return '<w:p><w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'
            .'<w:r><w:t xml:space="preserve">'.$this->xml($text).'</w:t></w:r></w:p>';
    }

    private function xml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'</Relationships>';
    }

    private function documentRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function coreProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/">'
            .'<dc:title>NovuPay OPS White Paper</dc:title>'
            .'<dc:subject>Operator of Payment System</dc:subject>'
            .'<dc:creator>NovuPay</dc:creator>'
            .'</cp:coreProperties>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .$this->style('Normal', 'Normal', true, '<w:sz w:val="22"/><w:szCs w:val="22"/>', '<w:spacing w:after="120" w:line="276" w:lineRule="auto"/>')
            .$this->style('Title', 'Title', false, '<w:b/><w:sz w:val="40"/><w:szCs w:val="40"/><w:color w:val="0F172A"/>', '<w:spacing w:before="0" w:after="160"/>')
            .$this->style('Subtitle', 'Subtitle', false, '<w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="334155"/>', '<w:spacing w:before="0" w:after="200"/>')
            .$this->style('Heading1', 'heading 1', false, '<w:b/><w:sz w:val="32"/><w:szCs w:val="32"/><w:color w:val="0F172A"/>', '<w:spacing w:before="360" w:after="80"/>')
            .$this->style('Heading2', 'heading 2', false, '<w:b/><w:sz w:val="26"/><w:szCs w:val="26"/><w:color w:val="0F172A"/>', '<w:spacing w:before="240" w:after="60"/>')
            .$this->style('Kicker', 'Kicker', false, '<w:i/><w:sz w:val="20"/><w:szCs w:val="20"/><w:color w:val="0560AA"/>', '<w:spacing w:before="0" w:after="80"/>')
            .$this->style('BodyText', 'Body Text', false, '<w:sz w:val="22"/><w:szCs w:val="22"/>', '<w:spacing w:after="160" w:line="276" w:lineRule="auto"/>')
            .$this->style('ListBullet', 'List Bullet', false, '<w:sz w:val="22"/><w:szCs w:val="22"/>', '<w:ind w:left="720"/><w:spacing w:after="80"/>')
            .'</w:styles>';
    }

    private function style(string $id, string $name, bool $default, string $run, string $paragraph): string
    {
        $defaultAttr = $default ? ' w:default="1"' : '';
        $basedOn = $default ? '' : '<w:basedOn w:val="Normal"/>';

        return '<w:style w:type="paragraph"'.$defaultAttr.' w:styleId="'.$id.'">'
            .'<w:name w:val="'.$this->xml($name).'"/>'
            .$basedOn
            .'<w:qFormat/>'
            .'<w:pPr>'.$paragraph.'</w:pPr>'
            .'<w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>'.$run.'</w:rPr>'
            .'</w:style>';
    }
}
