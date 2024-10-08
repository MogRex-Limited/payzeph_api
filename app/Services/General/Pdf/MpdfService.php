<?php

namespace App\Services\General\Pdf;

use Illuminate\View\View;
use Mpdf\Mpdf;

class MpdfService
{
    public $pdf, $watermark_url = null, $watermark_opacity = null;
    public function __construct(array $config = null)
    {
        $this->pdf = new Mpdf(array_merge([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'Arial',
            'default_font_size' => 12,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'orientation' => 'P',
            'watermarkImgBehind' => true, // Add this option to place the watermark behind the content
        ], $config ?? []));
    }


    public function  setWatermarkImage(?string $url, ?float $opacity = null)
    {
        $this->watermark_url = $url;
        $this->watermark_opacity = $opacity ?? 1.0;
        return $this;
    }

    public function generate(View $view)
    {
        if (!empty($this->watermark_url)) {
            $this->pdf->SetWatermarkImage($this->watermark_url, $this->watermark_opacity);
            $this->pdf->showWatermarkImage = true;
        }

        $this->pdf->WriteHTML($view->render());
        return $this;
    }

    public function output($name = null, $dest = null)
    {
        $this->pdf->Output($name, $dest);
        return $this;
    }

    public function save($name)
    {
        $this->pdf->OutputFile($name);
        return $this;
    }
}
