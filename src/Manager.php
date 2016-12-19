<?php

namespace MetaTags;

use DateTime;
use Exception;

class Manager
{
    /**
     * Package configuration values.
     *
     * @var array
     */
    protected $config = [
        'validate' => false,
        'twitter' => true,
        'truncate' => [
            'description' => 160,
            'twitter:title' => 70,
            'og:description' => 200,
            'twitter:description' => 200,
        ],
    ];

    /**
     * All the tags.
     *
     * @var array
     */
    protected $tags = [
        'general' => [
            'type' => [
                'value' => 'website',
                'attributes' => [],
            ],
        ],
        'twitter' => [],
    ];

    /**
     * Validation array.
     *
     * @var array
     */
    protected $validations = [
        'type' => [
            'article',
            'book',
            'profile',
            'website',
            'music.song',
            'music.album',
            'music.playlist',
            'music.radio_station',
            'video.movie',
            'video.episode',
            'video.tv_show',
            'video.other',
        ],

        'article' => [
            'published_time',
            'modified_time',
            'expiration_time',
            'author',
            'section',
            'tag',
        ],

        'audio' => [
            'secure_url',
            'type',
        ],

        'book' => [
            'author',
            'isbn',
            'release_date',
            'tag',
        ],

        'music.song' => [
            'duration',
            'album',
            'album:disc',
            'album:track',
            'musician',
        ],

        'music.album' => [
            'song',
            'song:disc',
            'song:track',
            'musician',
            'release_date',
        ],

        'music.playlist' => [
            'song',
            'song:disc',
            'song:track',
            'creator',
        ],

        'music.radio_station' => [
            'creator',
        ],

        'profile' => [
            'first_name',
            'last_name',
            'username',
            'gender',
        ],

        'video' => [
            'secure_url',
            'type',
            'width',
            'height',
            'actor',
            'role',
            'director',
            'writer',
            'duration',
            'release_date',
            'tag',
        ],

        'video.episode' => [
            'video:series',
        ],
    ];

    /**
     * Create a new Manager instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach($config as $name=>$value) {
            $this->config[$name] = $value;
        }
    }

    /**
     * Adds a custom tag to the list of tags.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function tag($name, $value)
    {
        $value = $this->convertDate($value);

        return $this->set($name, $value);
    }

    /**
     * Adds a type tag.
     *
     * @param string $type
     *
     * @return self
     * @throws Exception
     */
    public function type($type)
    {
        if ($this->config('validate', false) === true && in_array($type, $this->getValidators('type')) === false) {
            throw new Exception("Open Graph: Invalid type value '{$type}' (unknown type)");
        }

        return $this->set('type', $type);
    }

    /**
     * Adds a URL tag
     *
     * @param string $url
     *
     * @return self
     * @throws Exception
     */
    public function url($url = null)
    {
        if (!$url && isset($_SERVER['HTTP_HOST'])) {
            $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        }

        return $this->set('url', $url);
    }

    /**
     * Create the HTML for the tag.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return string
     */
    protected function renderTag($name, $value, array $attributes = [])
    {
        $output = [];

        // Truncation rules
        if ($limit = $this->config("truncate.{$name}")) {
            $value = $this->truncate($value, $limit);
        }

        // Only create the tag as long as it's not
        // just a namespace
        if ($value) {
            $output[] = str_replace(
                ['{{name}}', '{{value}}'],
                [$name, $value],
                '<meta property="{{name}}" content="{{value}}">'
            );
        }

        // Create tag attributes
        foreach ($attributes as $a => $v) {
            $output[] = $this->renderTag("{$name}:{$a}", $v);
        }

        return implode("\n", $output);
    }

    /**
     * Set tag data.
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return self
     * @throws Exception
     */
    public function set($name, $value, array $attributes = [])
    {
        // Skip empties
        if ($value === null && empty($attributes)) {
            return $this;
        }

        // Validate attributes
        if ($this->config('validate', false) === true && empty($attributes) === false) {
            $this->validation($name, $attributes);
        }

        // Validate URLs
        if (preg_match('/(url|image|video)$/im', $name)) {
            $value = $this->parseUrl($value, $name);
        }

        // Remove all tags
        $value = strip_tags($value);

        // Remove non-ASCII printable characters
        $value = preg_replace('/[^\x20-\x7E]/', '', $value);

        // Remove all excess white space
        $value = trim(preg_replace('!\s+!', ' ', $value));

        // Consider all tags to be general unless the prefix
        // matches a special value.
        $type = 'general';

        // Determine if the tag is Twitter specific
        if (substr($name, 0, 8) === 'twitter_') {
            $type = 'twitter';
            $name = str_replace('_', ':', substr($name, 8));

            // Ensure the tag is rendered
            $this->config['twitter'] = true;
        }

        // Set tag data
        $this->tags[$type][$name] = [
            'value' => $value,
            'attributes' => $attributes,
        ];

        return $this;
    }

    /**
     * Get a specific tag.
     *
     * @param string $key
     * @param string $type
     *
     * @return array|null
     */
    public function get($key, $type = 'general')
    {
        if (array_key_exists($key, $this->tags[$type])) {
            return $this->tags[$type][$key];
        }

        $array = $this->tags[$type];

        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            }
            else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Remove a specific tag.
     *
     * @param string $key
     * @param string $type
     */
    public function forget($key, $type = 'general')
    {
        unset($this->tags[$type][$key]);
    }

    /**
     * Get an array of value keys to validate.
     *
     * @param string $type
     * @param array  $attributes
     *
     * @return array
     * @throws Exception
     */
    public function validation($type, $attributes = [])
    {
        // Get base validators
        $validators = $this->getValidators($type);

        // Get all types
        if (($pos = strpos($type, '.')) !== false) {
            $validators = array_merge($validators, substr($type, 0, $pos));
        }

        // Validate each attribute key
        if (empty($validators) === false) {
            foreach ((array) $attributes as $name => $value) {
                if (in_array($name, $validators) === false) {
                    throw new Exception("Open Graph: Invalid attribute '{$name}' ({$type})");
                }
            }
        }

        return true;
    }

    /**
     * Get an array of value keys to validate.
     *
     * @param string $type
     *
     * @return array
     */
    protected function getValidators($type)
    {
        return array_key_exists($type, $this->validations)
            ? $this->validations[$type]
            : [];
    }

    /**
     * Converts a DateTime object to a string (ISO 8601)
     *
     * @param string|DateTime $date The date (string or DateTime)
     *
     * @return string
     */
    protected function convertDate($date)
    {
        if (is_a($date, 'DateTime')) {
            return (string)$date->format(DateTime::ISO8601);
        }

        return $date;
    }

    /**
     * Trim a string to a given number of characters.
     *
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    protected function truncate($string, $limit = 160)
    {
        // Include the padding character count
        $limit = $limit - 3;

        return strlen($string) > $limit
            ? substr($string, 0, $limit) . '...'
            : $string;
    }

    /**
     * Parse and validate the given URL.
     *
     * @param string $url
     * @param string $type
     *
     * @return string
     * @throws Exception
     */
    protected function parseUrl($url, $type)
    {
        // Check path for valid path and use the `asset` helper function if available.
        if (!preg_match('/^https?:\/\//', $url) && function_exists('asset')) {
            $url = asset($url);
        }

        if ($this->config('validate', false) === true && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Open Graph: Invalid {$type} URL '{$url}'");
        }

        return $url;
    }

    /**
     * Get a specific tag.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return array|null
     */
    public function config($key, $default = null)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        $array = $this->config;

        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            }
            else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Convert the manager to its string representation.
     *
     * @return string
     */
    protected function renderTwitter()
    {
        $output = [];

        // Render Twitter base tags
        foreach (['title', 'description'] as $name) {
            if ($tag = $this->get($name)) {
                $output[] = $this->renderTag("twitter:{$name}", $tag['value'], $tag['attributes']);
            }
        }

        // Twitter specific tags
        foreach ($this->tags['twitter'] as $name => $tag) {
            switch($name) {
                case 'image':
                    $output[] = $this->renderTwitterImage($tag);
                    break;
                case 'image:alt':
                    break;
                default:
                    $output[] = $this->renderTag("twitter:{$name}", $tag['value'], $tag['attributes']);
            }
        }

        // Render Twitter image tags
        if (array_key_exists('image', $this->tags['twitter']) === false
            && ($image = $this->get('image'))
        ) {
            $output[] = $this->renderTwitterImage($image);
        }

        return implode("\n", $output);
    }

    /**
     * Convert the Twitter image into tags.
     *
     * @param string $image
     *
     * @return string
     */
    public function renderTwitterImage($image = null)
    {
        $output = [];

        if ($image) {
            $output[] = $this->renderTag('twitter:image', $image['value'], $image['attributes']);

            // Include image alt
            if ($image_alt = $this->get('image:alt', 'twitter')) {
                $output[] = $this->renderTag('twitter:image:alt', $image_alt['value'], $image_alt['attributes']);
            }
        }

        return implode("\n", $output);
    }

    /**
     * Convert the tags array into proper HTML tags.
     *
     * @return string
     */
    public function __toString()
    {
        $output = [];

        // Render standard description tag
        if ($tag = $this->get('description')) {
            $output[] = $this->renderTag('description', $tag['value'], $tag['attributes']);
        }

        // Render Open Graph tags
        foreach ($this->tags['general'] as $name => $tag) {
            $output[] = $this->renderTag("og:{$name}", $tag['value'], $tag['attributes']);
        }

        // Render all twitter tags
        if ($this->config('twitter', true)) {
            $output[] = $this->renderTwitter();
        }

        return implode("\n", $output);
    }

    /**
     * Render all tags for Laravel.
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        // Dynamically add the title from the view title content block. This helps
        // keep the title setting out of the controllers.
        if (array_key_exists('title', $this->tags['general']) === false
            && $title = trim(preg_replace('!\s+!', ' ', app('view')->yieldContent('title')))
        ) {
            $this->set('title', $title);
        }

        return new \Illuminate\Support\HtmlString($this->__toString());
    }

    /**
     * Handle dynamic tag creation calls.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, array $parameters = [])
    {
        // Tags that are just name spaces
        if (in_array($method, ['article', 'book', 'profile'])) {
            return $this->set($method, null, $parameters[0]);
        }

        // Bulk Social media setter
        if ($method === 'twitter') {
            foreach($parameters[0] as $name => $value) {
                call_user_func_array([$this, 'set'], [
                    "twitter_{$name}",
                    $value
                ]);
            }

            return $this;
        }

        // Snake case the method
        $method = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method));

        // Create parameters for tag creation
        $parameters = array_merge([$method], $parameters);

        // Ensure there are enough arguments
        if (count($parameters) === 1) {
            throw new Exception("Missing argument 2 in the creation of the [{$method}] tag.");
        }

        // Create tag
        call_user_func_array([$this, 'set'], $parameters);

        return $this;
    }
}
