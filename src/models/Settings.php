<?php

namespace liquidbcn\languageredirect\models;

use craft\base\Model;
use craft\helpers\ProjectConfig;

class Settings extends Model
{
    public string $defaultLanguage = 'en-GB';
    public ?array $urls = null;

    public function rules(): array
    {
        return [
            ['defaultLanguage', 'required'],
            ['defaultLanguage', 'string'],
            ['urls', 'safe'],
        ];
    }

    /**
     * Converts editable table format to associative array
     * Handles both direct config file format and Craft's project config format
     */
    public function getUrlsAsAssociativeArray(): array
    {
        if (empty($this->urls)) {
            return [];
        }

        $urls = ProjectConfig::unpackAssociativeArrays($this->urls);

        if ($this->isAssociativeArray($urls)) {
            return $urls;
        }

        $result = [];
        foreach ($urls as $row) {
            if (is_array($row) && !empty($row['locale']) && !empty($row['url'])) {
                $result[$row['locale']] = $row['url'];
            }
        }

        return $result;
    }

    /**
     * Converts associative array to editable table format for the CP
     */
    public function getUrlsAsTableRows(): array
    {
        if (empty($this->urls)) {
            return [];
        }

        $urls = ProjectConfig::unpackAssociativeArrays($this->urls);

        if ($this->isAssociativeArray($urls)) {
            $result = [];
            foreach ($urls as $locale => $url) {
                $result[] = ['locale' => $locale, 'url' => $url];
            }

            return $result;
        }

        return $urls;
    }

    private function isAssociativeArray(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
