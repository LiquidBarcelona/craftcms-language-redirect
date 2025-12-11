<?php

namespace liquidbcn\languageredirect\models;

use Craft;
use craft\base\Model;
use craft\helpers\ProjectConfig;

class Settings extends Model
{
    public ?array $domains = null;

    // Legacy properties for backward compatibility
    public string $defaultLanguage = 'en-GB';
    public ?array $urls = null;

    public function rules(): array
    {
        return [
            ['domains', 'safe'],
            ['defaultLanguage', 'string'],
            ['urls', 'safe'],
        ];
    }

    public function getSitesGroupedByDomain(): array
    {
        $sites = Craft::$app->getSites()->getAllSites();
        $domains = [];

        foreach ($sites as $site) {
            $baseUrl = Craft::parseEnv($site->baseUrl);
            $parsed = parse_url($baseUrl);
            $host = $parsed['host'] ?? 'unknown';
            $path = $parsed['path'] ?? '/';

            if ($path === '') {
                $path = '/';
            }

            if (!isset($domains[$host])) {
                $domains[$host] = [
                    'host' => $host,
                    'sites' => [],
                ];
            }

            $domains[$host]['sites'][] = [
                'id' => $site->id,
                'name' => $site->name,
                'language' => $site->language,
                'path' => $path,
            ];
        }

        // Add metadata
        foreach ($domains as $host => &$domain) {
            $languages = array_unique(array_column($domain['sites'], 'language'));
            $domain['hasMultipleLanguages'] = count($languages) > 1;
            $domain['languages'] = $languages;
        }

        return $domains;
    }

    public function getDomainConfig(string $host): array
    {
        $domains = $this->domains ?? [];
        $domains = ProjectConfig::unpackAssociativeArrays($domains);

        if (isset($domains[$host])) {
            $config = $domains[$host];

            if (isset($config['urls'])) {
                $config['urls'] = ProjectConfig::unpackAssociativeArrays($config['urls']);
            }

            return $config;
        }

        return [
            'enabled' => false,
            'defaultLanguage' => '',
            'urls' => [],
        ];
    }

    public function isDomainEnabled(string $host): bool
    {
        $config = $this->getDomainConfig($host);

        return !empty($config['enabled']);
    }

    public function getDomainUrls(string $host): array
    {
        $config = $this->getDomainConfig($host);
        $urls = $config['urls'] ?? [];

        if (empty($urls)) {
            return [];
        }

        // Handle editable table format
        if ($this->isIndexedArray($urls)) {
            $result = [];
            foreach ($urls as $row) {
                if (is_array($row) && !empty($row['locale']) && !empty($row['url'])) {
                    $result[$row['locale']] = $row['url'];
                }
            }

            return $result;
        }

        return $urls;
    }

    public function getDomainDefaultLanguage(string $host): string
    {
        $config = $this->getDomainConfig($host);
        $defaultLanguage = $config['defaultLanguage'] ?? '';

        if ($defaultLanguage !== '') {
            return $defaultLanguage;
        }

        // Fallback to first available language from URL mappings
        $urls = $this->getDomainUrls($host);

        if (!empty($urls)) {
            return array_key_first($urls);
        }

        return '';
    }

    public function getUrlsForCurrentDomain(): array
    {
        $currentHost = $this->getCurrentHost();

        // Try new domain-based config first
        if ($this->isDomainEnabled($currentHost)) {
            return $this->getDomainUrls($currentHost);
        }

        // Fall back to legacy config
        return $this->getLegacyUrlsAsAssociativeArray();
    }

    public function getDefaultLanguageForCurrentDomain(): string
    {
        $currentHost = $this->getCurrentHost();

        // Try new domain-based config first
        if ($this->isDomainEnabled($currentHost)) {
            return $this->getDomainDefaultLanguage($currentHost);
        }

        // Fall back to legacy config
        return $this->defaultLanguage;
    }

    public function isCurrentDomainEnabled(): bool
    {
        $currentHost = $this->getCurrentHost();

        // If using new domain config
        if (!empty($this->domains)) {
            return $this->isDomainEnabled($currentHost);
        }

        // Legacy: enabled if urls are configured
        return !empty($this->urls);
    }

    private function getCurrentHost(): string
    {
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        $baseUrl = Craft::parseEnv($currentSite->baseUrl);
        $parsed = parse_url($baseUrl);

        return $parsed['host'] ?? '';
    }

    private function getLegacyUrlsAsAssociativeArray(): array
    {
        if (empty($this->urls)) {
            return [];
        }

        $urls = ProjectConfig::unpackAssociativeArrays($this->urls);

        if (!$this->isIndexedArray($urls)) {
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

    private function isIndexedArray(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
