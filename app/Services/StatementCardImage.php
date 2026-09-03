<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Restaurant;

/**
 * Builds a unique PNG "statement card" per customer for WhatsApp.
 * Requires PHP GD extension.
 */
class StatementCardImage
{
    public function generate(Customer $customer, ?Restaurant $restaurant, array $recentOrders = []): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $width = 900;
        $height = 1100;
        $im = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($im, 15, 23, 42);      // slate-900
        $card = imagecolorallocate($im, 255, 255, 255);
        $accent = imagecolorallocate($im, 245, 158, 11); // amber
        $text = imagecolorallocate($im, 15, 23, 42);
        $muted = imagecolorallocate($im, 100, 116, 139);
        $red = imagecolorallocate($im, 185, 28, 28);
        $line = imagecolorallocate($im, 226, 232, 240);

        imagefilledrectangle($im, 0, 0, $width, $height, $bg);
        imagefilledrectangle($im, 40, 40, $width - 40, $height - 40, $card);

        // Top accent bar
        imagefilledrectangle($im, 40, 40, $width - 40, 120, $bg);
        imagefilledrectangle($im, 40, 120, $width - 40, 128, $accent);

        $shop = $this->safe($restaurant?->name ?? 'Store');
        $this->text($im, 28, 70, $shop, 255, 255, 255, 5);
        $this->text($im, 28, 95, 'Account reminder', 203, 213, 225, 3);

        $y = 160;
        $this->text($im, 60, $y, 'Customer', 100, 116, 139, 3);
        $y += 28;
        $this->text($im, 60, $y, $this->safe($customer->name), 15, 23, 42, 5);

        $y += 40;
        $this->text($im, 60, $y, 'Phone: ' . $this->safe($customer->phone ?: '—'), 100, 116, 139, 3);

        $y += 50;
        imageline($im, 60, $y, $width - 60, $y, $line);
        $y += 30;

        $this->text($im, 60, $y, 'BALANCE DUE', 100, 116, 139, 3);
        $y += 36;
        $balance = 'Rs. ' . number_format((float) $customer->balance, 2);
        $this->text($im, 60, $y, $balance, 185, 28, 28, 5);

        $y += 50;
        imageline($im, 60, $y, $width - 60, $y, $line);
        $y += 30;

        $this->text($im, 60, $y, 'RECENT BILLS', 100, 116, 139, 3);
        $y += 34;

        if (empty($recentOrders)) {
            $this->text($im, 60, $y, 'No recent bills on file', 100, 116, 139, 3);
            $y += 28;
        } else {
            foreach (array_slice($recentOrders, 0, 6) as $order) {
                $label = ($order->order_number ?? 'Order')
                    . '  ·  '
                    . ($order->created_at?->format('d M Y') ?? '')
                    . '  ·  Rs. '
                    . number_format((float) ($order->total ?? 0), 2);
                $this->text($im, 60, $y, $this->safe($label), 15, 23, 42, 3);
                $y += 28;
                if ($y > $height - 160) {
                    break;
                }
            }
        }

        $y = $height - 130;
        imageline($im, 60, $y, $width - 60, $y, $line);
        $y += 28;
        $this->text($im, 60, $y, 'Please settle at your earliest convenience.', 100, 116, 139, 3);
        $y += 26;
        $this->text($im, 60, $y, 'Generated ' . now()->format('d M Y H:i') . ' · ' . $shop, 148, 163, 184, 2);

        $dir = storage_path('app/whatsapp-cards');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . '/customer-' . $customer->id . '-' . now()->format('YmdHis') . '.png';
        imagepng($im, $path);
        imagedestroy($im);

        return is_file($path) ? $path : null;
    }

    protected function safe(string $text): string
    {
        // GD built-in fonts are Latin-only; strip risky chars lightly
        return substr(preg_replace('/[^\x20-\x7E\x{00A0}-\x{024F}]/u', '', $text) ?: $text, 0, 80);
    }

    protected function text($im, int $x, int $y, string $string, int $r, int $g, int $b, int $size = 3): void
    {
        $color = imagecolorallocate($im, $r, $g, $b);
        // size 1-5 maps to built-in font
        $font = max(1, min(5, $size));
        imagestring($im, $font, $x, $y - 12, $string, $color);
    }
}
