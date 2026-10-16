<?php

namespace App\Services;

use App\Models\Order;

class BulkLabelPdf
{
    private const PAGE_WIDTH = 105 / 25.4 * 72;
    private const PAGE_HEIGHT = 145 / 25.4 * 72;
    private const MARGIN = 6 / 25.4 * 72;
    private const FONT_SIZE = 18.0; // 24 CSS px at 96 dpi.

    /** @param \Illuminate\Support\Collection<int, Order> $orders */
    public function make($orders, string $layout, array $fromAddress): string
    {
        $pageStreams = [];
        foreach ($orders as $order) {
            $pageStreams[] = $layout === 'split'
                ? $this->splitPage($order, $fromAddress)
                : $this->verticalPage($order, $fromAddress);
        }

        return $this->assemble($pageStreams);
    }

    private function verticalPage(Order $order, array $fromAddress): string
    {
        $to = $this->toLines($order);
        $from = array_merge(['FROM ,'], $fromAddress);
        $font = self::FONT_SIZE;
        $fromWidth = 80 / 25.4 * 72;
        $toWidth = self::PAGE_WIDTH - 2 * self::MARGIN;
        $gap = 4 / 25.4 * 72;

        for ($i = 0; $i < 8; $i++) {
            $toWrapped = $this->wrapLines($to, $toWidth, $font);
            $fromWrapped = $this->wrapLines($from, $fromWidth, $font);
            $leading = $font * 1.15;
            $needed = (count($toWrapped) + count($fromWrapped)) * $leading + $gap + 2 * $leading;
            $available = self::PAGE_HEIGHT - 2 * self::MARGIN;
            if ($needed <= $available || $font <= 9) break;
            $font = max(9, $font * ($available / $needed));
        }

        $toWrapped = $this->wrapLines($to, $toWidth, $font);
        $fromWrapped = $this->wrapLines($from, $fromWidth, $font);
        $leading = $font * 1.15;
        $x = self::MARGIN;
        $y = self::PAGE_HEIGHT - self::MARGIN - $leading;
        $stream = '';
        foreach ($toWrapped as $line) {
            $stream .= $this->text($x, $y, $line, $font);
            $y -= $leading;
        }

        $y -= $leading * 0.35;
        $stream .= sprintf("[3 2] 0 d %.2F %.2F m %.2F %.2F l S [] 0 d\n", $x, $y, self::PAGE_WIDTH - self::MARGIN, $y);
        $y -= $leading;
        $fromX = self::PAGE_WIDTH - self::MARGIN - $fromWidth;
        foreach ($fromWrapped as $line) {
            $stream .= $this->text($fromX, $y, $line, $font);
            $y -= $leading;
        }

        return $stream;
    }

    private function splitPage(Order $order, array $fromAddress): string
    {
        $font = self::FONT_SIZE;
        $gap = 8 / 25.4 * 72;
        $columnWidth = (self::PAGE_WIDTH - 2 * self::MARGIN - $gap) / 2;
        $to = $this->toLines($order);
        $from = array_merge(['FROM ,'], $fromAddress);

        for ($i = 0; $i < 8; $i++) {
            $toWrapped = $this->wrapLines($to, $columnWidth, $font);
            $fromWrapped = $this->wrapLines($from, $columnWidth, $font);
            $needed = max(count($toWrapped), count($fromWrapped)) * $font * 1.15;
            $available = self::PAGE_HEIGHT - 2 * self::MARGIN;
            if ($needed <= $available || $font <= 8) break;
            $font = max(8, $font * ($available / $needed));
        }

        $toWrapped = $this->wrapLines($to, $columnWidth, $font);
        $fromWrapped = $this->wrapLines($from, $columnWidth, $font);
        $leading = $font * 1.15;
        $top = self::PAGE_HEIGHT - self::MARGIN - $leading;
        $stream = '';
        foreach ([[$toWrapped, self::MARGIN], [$fromWrapped, self::MARGIN + $columnWidth + $gap]] as [$lines, $x]) {
            $y = $top;
            foreach ($lines as $line) {
                $stream .= $this->text($x, $y, $line, $font);
                $y -= $leading;
            }
        }

        return $stream;
    }

    private function toLines(Order $order): array
    {
        $lines = ['TO ,', mb_strtoupper((string) ($order->customer_name ?? ''), 'UTF-8')];
        $houseStreet = collect([$order->house_building, $order->street])->filter()->implode(' - ');
        $areaCity = collect([$order->area, $order->city])->filter()->implode(', ');
        $districtStatePin = collect([$order->district, $order->state])->filter()->implode(', ');
        if ($order->pin_code) $districtStatePin = trim($districtStatePin.', PIN: '.$order->pin_code, ', ');

        foreach ([$houseStreet, $areaCity, $districtStatePin, $order->customer_phone ? 'PH: '.$order->customer_phone : null] as $line) {
            if (filled($line)) $lines[] = (string) $line;
        }

        return $lines;
    }

    private function wrapLines(array $lines, float $width, float $font): array
    {
        $maxChars = max(8, (int) floor($width / ($font * 0.56)));
        $wrapped = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') continue;
            $parts = preg_split('/\s+/u', $line) ?: [$line];
            $current = '';
            foreach ($parts as $part) {
                while (mb_strlen($part, 'UTF-8') > $maxChars) {
                    if ($current !== '') { $wrapped[] = $current; $current = ''; }
                    $wrapped[] = mb_substr($part, 0, $maxChars, 'UTF-8');
                    $part = mb_substr($part, $maxChars, null, 'UTF-8');
                }
                $candidate = $current === '' ? $part : $current.' '.$part;
                if (mb_strlen($candidate, 'UTF-8') > $maxChars && $current !== '') {
                    $wrapped[] = $current;
                    $current = $part;
                } else {
                    $current = $candidate;
                }
            }
            if ($current !== '') $wrapped[] = $current;
        }

        return $wrapped;
    }

    private function text(float $x, float $y, string $value, float $font): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $encoded = $encoded === false ? '' : $encoded;
        $encoded = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
        return sprintf("BT /F1 %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n", $font, $x, $y, $encoded);
    }

    private function assemble(array $streams): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $kids = [];
        foreach ($streams as $index => $stream) {
            $pageId = 4 + $index * 2;
            $contentId = $pageId + 1;
            $kids[] = $pageId.' 0 R';
            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $contentId
            );
            $objects[$contentId] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}endstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxId + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
