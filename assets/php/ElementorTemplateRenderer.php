<?php
/**
 * ElementorTemplateRenderer
 *
 * Fills the `$__placeholder__!` tokens inside the Elementor export JSON files
 * under assets/template/elementor/ with the real data of a project, so the
 * result can be imported straight into WordPress.
 *
 * Placeholders live inside JSON string values, so every replacement value is
 * escaped as a JSON string fragment before substitution. That keeps the file
 * valid JSON even when the data contains quotes, backslashes or newlines.
 */
class ElementorTemplateRenderer
{
    /** Root folder holding one sub folder per template. */
    private $templateRoot;

    /** @var array<string,string> placeholder name => replacement value */
    private $values = array();

    public function __construct($templateRoot = null)
    {
        $this->templateRoot = $templateRoot !== null
            ? rtrim($templateRoot, "/\\")
            : dirname(__DIR__) . '/template/elementor';
    }

    /**
     * Maps a project row + its template page details onto placeholder values.
     *
     * @param array $project      row from tb_project (joined with country name)
     * @param array $pageDetails  decoded templatepagedetails JSON, keyed by page
     * @param string $openingHours already formatted opening hours text
     */
    public function setProjectData(array $project, array $pageDetails, $openingHours = '')
    {
        $shopName = $this->pick($project, 'projectName');
        $address  = $this->pick($project, 'address');

        $home = isset($pageDetails['home']) && is_array($pageDetails['home'])
            ? $pageDetails['home']
            : array();

        // Google review links live in the Home page JSON, under different keys
        // depending on which template form captured them.
        $ggReview = $this->firstNonEmpty($home, array('A2-17-LinkReview', 'A3-13-LinkGoogleReview'));
        $writeUs  = $this->firstNonEmpty($home, array('A2-18-LinkWrite'));

        $this->values = array(
            'shopName'      => $shopName,
            'city'          => $this->pick($project, 'city'),
            'facebook'      => $this->pick($project, 'facebookURL'),
            'instagram'     => $this->pick($project, 'instagramURL'),
            'email'         => $this->pick($project, 'email'),
            'localtion'     => $address,
            'mapLink'       => $this->buildMapLink($shopName, $address),
            'opening'       => $this->plainText($openingHours),
            'phone'         => $this->pick($project, 'phone'),
            'ggReview'      => $ggReview,
            'writeUsReview' => $writeUs,
            'order'         => $this->pick($project, 'orderURL'),
            'table'         => $this->pick($project, 'tableURL'),
        );
    }

    /** Overrides or adds a single placeholder value. */
    public function setValue($name, $value)
    {
        $this->values[$name] = (string)$value;
    }

    /** @return array<string,string> the resolved placeholder map */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Picks the template folder for a project.
     * shopType "Restaurant" + selectedTemplate 1 => RestaurantTemplate1
     */
    public function resolveTemplateFolder($shopType, $selectedTemplate)
    {
        $number = (int)$selectedTemplate;
        if ($number < 1) {
            $number = 1;
        }
        $folder = ucfirst(strtolower(trim((string)$shopType))) . 'Template' . $number;
        $path = $this->templateRoot . '/' . $folder;

        return is_dir($path) ? $folder : null;
    }

    /** Lists the JSON files of a template folder, keyed by file name. */
    public function listTemplateFiles($folder)
    {
        $path = $this->templateRoot . '/' . $this->safeFolder($folder);
        if (!is_dir($path)) {
            return array();
        }

        $files = array();
        foreach (glob($path . '/*.json') as $file) {
            $files[basename($file)] = $file;
        }
        ksort($files);

        return $files;
    }

    /** Reads one template file and returns it with every placeholder replaced. */
    public function renderFile($folder, $fileName)
    {
        $files = $this->listTemplateFiles($folder);
        if (!isset($files[$fileName])) {
            return null;
        }

        $content = file_get_contents($files[$fileName]);

        return $content === false ? null : $this->replace($content);
    }

    /** Renders every file of a template folder. @return array name => content */
    public function renderAll($folder)
    {
        $rendered = array();
        foreach ($this->listTemplateFiles($folder) as $name => $path) {
            $content = file_get_contents($path);
            if ($content !== false) {
                $rendered[$name] = $this->replace($content);
            }
        }

        return $rendered;
    }

    /**
     * Replaces all `$__name__!` tokens. Matching is case insensitive so that
     * both `$__mapLink__!` and `$__maplink__!` resolve to the same value.
     */
    public function replace($content)
    {
        $self = $this;

        return preg_replace_callback(
            '/\$__([A-Za-z0-9_]+)__!/',
            function ($matches) use ($self) {
                return $self->valueFor($matches[1]);
            },
            $content
        );
    }

    /** Resolves one placeholder name to its JSON escaped replacement. */
    public function valueFor($name)
    {
        $lookup = strtolower($name);
        foreach ($this->values as $key => $value) {
            if (strtolower($key) === $lookup) {
                return $this->jsonFragment($value);
            }
        }

        // Unknown placeholder: blank it out rather than leaking the raw token
        // into WordPress.
        return '';
    }

    /** Counts the placeholders present in a string, for diagnostics. */
    public function findPlaceholders($content)
    {
        preg_match_all('/\$__([A-Za-z0-9_]+)__!/', $content, $matches);

        return array_count_values($matches[1]);
    }

    /**
     * Escapes a value for use inside an existing JSON string literal.
     * json_encode gives us the quoted form, we drop the surrounding quotes.
     */
    private function jsonFragment($value)
    {
        $encoded = json_encode((string)$value, JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return '';
        }

        return substr($encoded, 1, -1);
    }

    /** Builds a Google Maps search URL from the shop name and address. */
    private function buildMapLink($shopName, $address)
    {
        $query = trim($shopName . ' ' . $address);
        if ($query === '') {
            return '';
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
    }

    /** Turns the HTML formatted opening hours into single line plain text. */
    private function plainText($html)
    {
        $text = preg_replace('/<br\s*\/?>/i', ' | ', (string)$html);
        $text = strip_tags($text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Keeps a caller supplied folder name inside the template root. */
    private function safeFolder($folder)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', (string)$folder);
    }

    private function pick(array $row, $key)
    {
        return isset($row[$key]) && $row[$key] !== null ? trim((string)$row[$key]) : '';
    }

    private function firstNonEmpty(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (!empty($row[$key])) {
                return trim((string)$row[$key]);
            }
        }

        return '';
    }
}
