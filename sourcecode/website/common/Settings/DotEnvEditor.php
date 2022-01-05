<?php namespace Common\Settings;

use Dotenv\Dotenv;
use Dotenv\Repository\AdapterRepository;
use Dotenv\Repository\RepositoryBuilder;
use Str;

class DotEnvEditor
{
    /**
     * Load values from .env file
     *
     * @param string|null $path
     * @param string $fileName
     * @return array
     */
    public function load($fileName = '.env', $path = null)
    {
        $path = $path ?: base_path();

        $dotEnv = Dotenv::create(RepositoryBuilder::createWithNoAdapters()->make(), [$path], $fileName);
        $values = $dotEnv->load();
        $lowercaseValues = [];

        foreach ($values as $key => $value) {
            if (strtolower($value) === 'null') {
                $lowercaseValues[strtolower($key)] = null;
            } else if (strtolower($value) === 'false') {
                $lowercaseValues[strtolower($key)] = false;
            } else if (strtolower($value) === 'true') {
                $lowercaseValues[strtolower($key)] = true;
            } else if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                $lowercaseValues[strtolower($key)] = $matches[2];
            } else {
                $lowercaseValues[strtolower($key)] = $value;
            }
        }

        return $lowercaseValues;
    }

    /**
     * Write specified settings to .env file.
     *
     * @param array $values
     * @param string $fileName
     * @return void
     */
    public function write($values = [], $fileName = '.env')
    {
        $content = file_get_contents(base_path($fileName));

        foreach ($values as $key => $value) {
            $value = $this->formatValue($value);
            $key = strtoupper($key);

            if (Str::contains($content, $key.'=')) {
                preg_match("/($key=)(.*?)(\n|\Z)/msi", $content, $matches);
                $content = str_replace($matches[1].$matches[2], $matches[1].$value, $content);
            } else {
                $content .= "\n$key=$value";
            }
        }

        file_put_contents(base_path($fileName), $content);
    }

    /**
     * Format specified value to be compatible with .env file
     *
     * @param string|null $value
     * @return string
     */
    private function formatValue($value)
    {
        if ($value === 0 || $value === false) $value = 'false';
        if ($value === 1 || $value === true) $value = 'true';
        if ( ! $value) $value = 'null';
        $value = trim($value);

        // wrap string in quotes, if it contains whitespace or special characters
        if (preg_match('/\s/', $value) || Str::contains($value, '#')) {
            //replace double quotes with single quotes
            $value = str_replace('"', "'", $value);

            //wrap string in quotes
            $value = '"'.$value.'"';
        }

        return $value;
    }
}
