<?php

namespace App\Services;

use App\Models\Order;

class BulkLabelPdf
{
    private const PAGE_WIDTH = 105 / 25.4 * 72;
    private const PAGE_HEIGHT = 145 / 25.4 * 72;
    private const MARGIN = 6 / 25.4 * 72;
    private const FONT_SIZE = 18.0; // 24 CSS px at 96 dpi.
    private const SENDER_FONT_SIZE = 15.75; // 21 CSS px at 96 dpi.

    /** @param \Illuminate\Support\Collection<int, Order> $orders */
    public function make($orders, string $layout, array $fromAddress, string $customerId): string
    {
        $pageStreams = [];
        foreach ($orders as $order) {
            $pageStreams[] = $layout === 'split'
                ? $this->splitPage($order, $fromAddress, $customerId)
                : $this->verticalPage($order, $fromAddress, $customerId);
        }

        return $this->assemble($pageStreams);
    }

    private function verticalPage(Order $order, array $fromAddress, string $customerId): string
    {
        $to = $this->toLines($order);
        $font = self::FONT_SIZE;
        $fromWidth = 80 / 25.4 * 72;
        $toWidth = self::PAGE_WIDTH - 2 * self::MARGIN;

        $toWrapped = $this->wrapLines($to, $toWidth, $font);
        $fromHeaderWrapped = $this->wrapLines(['From:'], $fromWidth, $font);
        $senderWrapped = $this->wrapLines($fromAddress, $fromWidth, self::SENDER_FONT_SIZE);
        $customerIdWrapped = $this->wrapLines([$this->customerIdLine($customerId)], $fromWidth, $font);
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
        foreach ([[$fromHeaderWrapped, $font], [$senderWrapped, self::SENDER_FONT_SIZE]] as [$lines, $lineFont]) {
            $lineLeading = $lineFont * 1.15;
            foreach ($lines as $line) {
                $stream .= $this->text($fromX, $y, $line, $lineFont);
                $y -= $lineLeading;
            }
        }
        $separatorY = $y + 2;
        $stream .= sprintf("[3 2] 0 d %.2F %.2F m %.2F %.2F l S [] 0 d\n", $fromX, $separatorY, $fromX + $fromWidth, $separatorY);
        $customerY = $separatorY - (2 / 25.4 * 72) - ($font * 1.15);
        foreach ($customerIdWrapped as $line) {
            $stream .= $this->text($fromX, $customerY, $line, $font);
            $customerY -= $leading;
        }

        return $stream;
    }

    private function splitPage(Order $order, array $fromAddress, string $customerId): string
    {
        $font = self::FONT_SIZE;
        $gap = 8 / 25.4 * 72;
        $columnWidth = (self::PAGE_WIDTH - 2 * self::MARGIN - $gap) / 2;
        $to = $this->toLines($order);

        $toWrapped = $this->wrapLines($to, $columnWidth, $font);
        $fromHeaderWrapped = $this->wrapLines(['From:'], $columnWidth, $font);
        $senderWrapped = $this->wrapLines($fromAddress, $columnWidth, self::SENDER_FONT_SIZE);
        $customerIdWrapped = $this->wrapLines([$this->customerIdLine($customerId)], $columnWidth, $font);
        $leading = $font * 1.15;
        $top = self::PAGE_HEIGHT - self::MARGIN - $leading;
        $stream = '';
        foreach ($toWrapped as $line) {
            $stream .= $this->text(self::MARGIN, $top, $line, $font);
            $top -= $leading;
        }

        $fromX = self::MARGIN + $columnWidth + $gap;
        $fromY = self::PAGE_HEIGHT - self::MARGIN - $leading;
        foreach ([[$fromHeaderWrapped, $font], [$senderWrapped, self::SENDER_FONT_SIZE]] as [$lines, $lineFont]) {
            $lineLeading = $lineFont * 1.15;
            foreach ($lines as $line) {
                $stream .= $this->text($fromX, $fromY, $line, $lineFont);
                $fromY -= $lineLeading;
            }
        }
        $separatorY = $fromY + 2;
        $stream .= sprintf("[3 2] 0 d %.2F %.2F m %.2F %.2F l S [] 0 d\n", $fromX, $separatorY, $fromX + $columnWidth, $separatorY);
        $customerY = $separatorY - (2 / 25.4 * 72) - ($font * 1.15);
        foreach ($customerIdWrapped as $line) {
            $stream .= $this->text($fromX, $customerY, $line, $font);
            $customerY -= $leading;
        }

        return $stream;
    }

    private function toLines(Order $order): array
    {
        $lines = ['TO ,', mb_strtoupper((string) ($order->customer_name ?? ''), 'UTF-8')];
        $houseStreet = collect([$order->house_building, $order->street])->filter()->implode(' - ');
        $area = trim((string) ($order->area ?? ''));
        if ($order->order_source === 'manual' && preg_match('/^counter\s+sale$/i', $area)) $area = '';
        $areaCity = collect([$area, $order->city])->filter()->implode(', ');
        $districtStatePin = collect([$order->district, $order->state])->filter()->implode(', ');
        if ($order->pin_code) $districtStatePin = trim($districtStatePin.', PIN: '.$order->pin_code, ', ');

        foreach ([$houseStreet, $areaCity, $districtStatePin, $order->customer_phone ? 'PH: '.$order->customer_phone : null] as $line) {
            if (filled($line)) $lines[] = (string) $line;
        }

        return $lines;
    }

    private function customerIdLine(string $customerId): string
    {
        return 'Customer Id: '.$customerId;
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
