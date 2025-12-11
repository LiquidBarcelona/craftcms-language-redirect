<?php

namespace liquidbcn\languageredirect;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use koenster\PHPLanguageDetection\BrowserLocalization;
use liquidbcn\languageredirect\models\Settings;

class LanguageRedirector extends Plugin
{
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();

        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->redirect();
        }
    }

    protected function redirect(): void
    {
        $request = Craft::$app->getRequest();
        $method = $request->getMethod();

        if ($method !== 'GET' && $method !== 'HEAD') {
            return;
        }

        $requestUrl = $request->getUrl();
        $url = parse_url($requestUrl);

        if (!empty($url['path']) && $url['path'] !== '/') {
            return;
        }

        $settings = $this->getSettings();

        if (!$settings->isCurrentDomainEnabled()) {
            return;
        }

        $urls = $settings->getUrlsForCurrentDomain();

        if (empty($urls)) {
            return;
        }

        $defaultLanguage = $settings->getDefaultLanguageForCurrentDomain();

        $browser = $request->getHeaders()->get('accept-language');
        $browserLoc = new BrowserLocalization();
        $browserLoc->setAvailable(array_keys($urls))
            ->setDefault($defaultLanguage)
            ->setPreferences($browser);

        $language = $browserLoc->detect();

        if (isset($urls[$language])) {
            Craft::$app->getResponse()->redirect($urls[$language], 301)->send();
            Craft::$app->end();
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'language-redirect/settings',
            ['settings' => $this->getSettings()]
        );
    }
}
