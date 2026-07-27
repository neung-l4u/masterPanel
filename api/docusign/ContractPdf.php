<?php
/**
 * ContractPdf — renders an existing HTML agreement template to PDF bytes.
 *
 * The agreement templates under modules/signup/assets/docs/ are plain PHP pages
 * driven by $_REQUEST. Rather than duplicating their markup, this class runs a
 * template with a synthetic $_REQUEST, captures the HTML, and hands it to Dompdf.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ContractPdf
{
    /** Agreement type => template path, relative to the project root. */
    private const TEMPLATES = [
        'pushpos'   => 'modules/signup/assets/docs/pushpos_agreement_V02.php',
        'marketing' => 'modules/signup/assets/docs/contract_2024_V02.php',
    ];

    private string $projectRoot;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = rtrim($projectRoot ?? dirname(__DIR__, 2), '/\\');
    }

    /**
     * Render an agreement to raw PDF bytes.
     *
     * @param string $type   One of the keys in self::TEMPLATES.
     * @param array  $fields Form fields the template reads from $_REQUEST.
     */
    public function render(string $type, array $fields): string
    {
        return $this->htmlToPdf($this->renderHtml($type, $fields));
    }

    /**
     * Same as render(), base64-encoded for the DocuSign documentBase64 field.
     */
    public function renderBase64(string $type, array $fields): string
    {
        return base64_encode($this->render($type, $fields));
    }

    /**
     * Execute an agreement template and capture its HTML output.
     */
    public function renderHtml(string $type, array $fields): string
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new InvalidArgumentException("Unknown agreement type '{$type}'");
        }

        $templatePath = $this->projectRoot . '/' . self::TEMPLATES[$type];

        if (!is_file($templatePath)) {
            throw new RuntimeException("Agreement template not found at {$templatePath}");
        }

        // The templates read $_REQUEST directly, so swap it for the duration of
        // the include and restore the originals afterwards.
        $savedRequest = $_REQUEST;
        $savedGet     = $_GET;

        // Tells the template to emit DocuSign anchor strings instead of the
        // blank signature placeholder images.
        $fields['docusignMode'] = '1';

        $_REQUEST = $fields;
        $_GET     = $fields;

        try {
            ob_start();
            include $templatePath;
            $html = (string) ob_get_clean();
        } catch (Throwable $e) {
            // ob_get_clean() may not have run; discard the buffer if it is still open.
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw new RuntimeException("Failed to render agreement template: {$e->getMessage()}", 0, $e);
        } finally {
            $_REQUEST = $savedRequest;
            $_GET     = $savedGet;
        }

        if (trim($html) === '') {
            throw new RuntimeException("Agreement template produced no output for type '{$type}'");
        }

        return $this->prepareForPdf($html);
    }

    /**
     * Convert an HTML string to PDF bytes.
     */
    public function htmlToPdf(string $html): string
    {
        $options = new Options();
        // The templates pull Bootstrap from a CDN and reference local images.
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $this->projectRoot);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdf = $dompdf->output();

        if ($pdf === null || $pdf === '') {
            throw new RuntimeException('Dompdf produced an empty PDF');
        }

        return $pdf;
    }

    /**
     * Rewrite the template HTML so Dompdf can resolve its assets:
     *  - local <img src> become absolute filesystem paths
     *  - the Bootstrap CDN <link> is dropped (Dompdf ignores most of it anyway
     *    and fetching it on every render is slow)
     */
    private function prepareForPdf(string $html): string
    {
        $docsDir = $this->projectRoot . '/modules/signup/assets/docs/';

        // Drop the CDN stylesheet and scripts. Dompdf does not execute JS, and
        // fetching remote assets on every render only adds latency.
        $html = preg_replace(
            [
                '#<link[^>]+cdn\.jsdelivr\.net[^>]*>#i',
                '#<script[^>]*\bsrc="[^"]*cdn\.jsdelivr\.net[^"]*"[^>]*>\s*</script>#is',
            ],
            '',
            $html
        ) ?? $html;

        // Relative image sources in the templates are siblings of the template file.
        $html = preg_replace_callback(
            '#(<img[^>]+src=")(?!https?://|data:|/)([^"]+)(")#i',
            static function (array $m) use ($docsDir): string {
                $absolute = $docsDir . $m[2];
                return is_file($absolute) ? $m[1] . $absolute . $m[3] : $m[1] . $m[2] . $m[3];
            },
            $html
        ) ?? $html;

        return $html;
    }

    /**
     * Agreement types this renderer supports.
     *
     * @return string[]
     */
    public static function supportedTypes(): array
    {
        return array_keys(self::TEMPLATES);
    }
}
